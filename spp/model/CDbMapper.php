<?php

namespace spp\model;

use spp\model\CConnMgr;
use spp\model\CMapper;

class CDbMapper implements CMapper
{

    static $op = ['>' => '>', '>=' => '>=', '<' => '<', '<=' => '<=',
        '=' => '=', '!=' => '!=', 'like' => 'like', 'is' => 'is', 'is not' => 'is not'
    ];
    private static $instances = array();
    private static $lastQuery = '';
    private $table = null;
    private $pk = null;
    private $pdo = null;
    private $condition = '';
    private $limit = '';
    private $group = '';
    private $order = '';
    private $update = '';
    private $needLeftBracketNum = 0;

    /**
     * 
     * @param string $table mysql table name
     * @param string $pk mysql table primary key
     */
    protected function __construct($table, $pk = 'id')
    {
        $this->table = "`" . $table . "`";
        $this->pk = $pk;
        if (isset(\Config::$db)) {
            $this->pdo = CConnMgr::getInstance()->pdo(\Config::$db);
        }
    }

    /**
     * 
     * @return new Object
     */
    public static function newInstance()
    {
        $caller = get_called_class();
        return new $caller();
    }

    /**
     * get last query string
     * @return string 
     */
    public static function getLastQuery()
    {
        return self::$lastQuery;
    }

    /**
     * commit trasaction
     * @param object $pdo 
     * @return 
     * @throws CModelException
     */
    public static function commit($pdo = null)
    {
        if ($pdo == null) {
            if (!isset(\Config::$db)) {
                throw new CModelException('db config not set');
            }
            $pdo = CConnMgr::getInstance()->pdo(\Config::$db);
        }
        return $pdo->commit();
    }

    /**
     * rollback trasaction
     * @param Object $pdo
     * @return type
     * @throws CModelException
     */
    public static function rollBack($pdo = null)
    {
        if ($pdo == null) {
            if (!isset(\Config::$db)) {
                throw new CModelException('db config not set');
            }
            $pdo = CConnMgr::getInstance()->pdo(\Config::$db);
        }
        return $pdo->rollBack();
    }

    /**
     * start trasaction
     * @param Object $pdo
     * @return type
     * @throws CModelException
     */
    public static function beginTransaction($pdo = null)
    {
        if ($pdo == null) {
            if (!isset(\Config::$db)) {
                throw new CModelException('db config not set');
            }
            $pdo = CConnMgr::getInstance()->pdo(\Config::$db);
        }

        return $pdo->beginTransaction();
    }

    private static function checkOperater($op)
    {
        return self::$op[$op] != null;
    }

    private static function checkBoolean($bool)
    {
        if ($bool == 'and' || $bool == 'or') {
            return true;
        }
        return false;
    }

    /**
     * get a Dao instance by table name and primary key
     * @param string $table
     * @param string $pk
     * @return dao instance
     */
    public static function getInstance($table, $pk = 'id')
    {
        $key = $table . "." . $pk;
        if (self::$instances[$key] == null) {
            self::$instances[$key] = new self($table, $pk);
        }
        return self::$instances[$key];
    }

    /**
     * set another pdo for dbmapper
     * @param Object $pdo
     */
    function setPdo($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * get data by primary key from mysql
     * @param string $id
     * @param array $columns
     * @return data list
     * @throws CModelException
     */
    public function findByPk($id, $columns = ['*'])
    {
        if ($this->pdo == null) {
            throw new CModelException("pdo is null");
        }

        return $this->where($this->pk, '=', $id)->findOne($columns);
    }

    /**
     * add sql where condition
     * where('type','=','sports')->where('age','=',30)
     * @param string $column
     * @param string $op
     * @param mixed $value
     * @param string $bool
     * @return $this
     * @throws CModelException
     */
    public function where($column, $op, $value, $bool = 'and')
    {
        if (!self::checkOperater($op) || !self::checkBoolean($bool)) {
            throw new CModelException("invalid op or bool param");
        }

        if ($this->condition == '') {
            $column = "`$column`";
            $value = "'" . addslashes($value) . "'";

            $this->condition = ' where ';
            for($i=0;$i<$this->needLeftBracketNum;++$i) {
                $this->condition .= "(";
            }
            $this->needLeftBracketNum = 0;
            
            $this->condition .= "$column $op $value";

        } else {

            $column = "`$column`";
            $value = "'" . addslashes($value) . "'";
            $this->condition .= " $bool ";
            for($i=0;$i<$this->needLeftBracketNum;++$i) {
                $this->condition .= "(";
            }
            $this->needLeftBracketNum = 0;
            $this->condition .= "$column $op $value";
        }
        return $this;
    }

    /**
     * add sql raw where condition
     * whereRaw('age > 100')
     * @param string $expr
     * @param string $bool
     * @return $this
     */
    public function whereRaw($expr, $bool = 'and')
    {
        if ($this->condition == '') {

            $this->condition = ' where ';
            
            for($i=0;$i<$this->needLeftBracketNum;++$i) {
                $this->condition .= "(";
            }
            $this->needLeftBracketNum = 0;

            $this->condition .= $expr;
        } else {

            $this->condition .= " $bool ";
            
            for($i=0;$i<$this->needLeftBracketNum;++$i) {
                $this->condition .= "(";
            }
            $this->needLeftBracketNum = 0;
            $this->condition .= $expr;
        }
        return $this;
    }

    /**
     * add "(" for sql
     * @return $this
     */
    public function addLeft()
    {
        $this->needLeftBracketNum++;
        return $this;
    }
    /**
     * add ")" for sql
     * @return $this
     */
    public function addRight()
    {
        $this->condition .= ')';
        return $this;
    }

    /**
     * limit count
     * @param int $count need count
     * @param int $offset offset
     * @return $this
     */
    public function limit($count, $offset = 0)
    {
        $this->limit = " limit $offset,$count";
        return $this;
    }

    /**
     * group by $column
     * @param string $column
     * @return $this
     */
    public function groupBy($column)
    {
        $this->group = " group by $column";
        return $this;
    }

    /**
     * order by column desc or asc
     * @param string $column
     * @param string $order
     * @return $this
     */
    public function orderBy($column, $order = 'desc')
    {
        if ($this->order == '') {
            $this->order = " order by `$column` $order";
        } else {
            $this->order .= ",`$column` $order";
        }

        return $this;
    }

    /**
     * order by field
     * @param string $field
     * @param string $fieldValue
     * @return $this
     */
    public function orderByField($field, $fieldValue)
    {
        $this->order = " order by field( $field ," . implode(',',$fieldValue) . ")";

        return $this;
    }

    public function orderByRand()
    {
        $this->order = " order by rand()";

        return $this;
    }

    public function clearQuery()
    {
        $this->condition = '';
        $this->order = '';
        $this->limit = '';
        $this->group = '';
        $this->update = '';
    }

    /**
     * get one data from mysql
     * where('age','>',30)->findOne(['name,age,birth']);
     * @param array $columns
     * @return data object
     * @throws CModelException
     */
    public function findOne($columns = ['*'])
    {
        if ($this->pdo == null) {
            throw new CModelException("pdo is null");
        }
        
        if (!is_array($columns) || count($columns) == 0) {
            throw new CModelException("param columns is not a array or array is empty");
        }
        
        $this->limit(1);
        self::$lastQuery = '';
        $query = 'select ' . $this->getSelectList($columns) . ' from ' . $this->table . $this->condition . $this->group . $this->order . $this->limit;
        self::$lastQuery = $query;
        $sth = $this->pdo->prepare($query);
        $sth->execute();
        $obj = $sth->fetch(\PDO::FETCH_OBJ);
        $this->clearQuery();
        return $obj;
    }

    /**
     * get data list by condition
     * where('age','>',30)->find(['name,age,birth']);
     * @param array $columns
     * @return data list objects
     * @throws CModelException
     */
    public function find($columns = ['*'])
    {
        if ($this->pdo == null) {
            throw new CModelException("pdo is null");
        }
        
        if (!is_array($columns) || count($columns) == 0) {
            throw new CModelException("param columns is not a array or array is empty");
        }
        
        self::$lastQuery = '';
        $query = 'select ' . $this->getSelectList($columns) . ' from ' . $this->table . $this->condition . $this->group . $this->order . $this->limit;
        self::$lastQuery = $query;
        $sth = $this->pdo->prepare($query);
        $sth->execute();
        $objs = $sth->fetchAll(\PDO::FETCH_OBJ);
        $this->clearQuery();
        return $objs;
    }

    /**
     * count by contion
     * where('field','>',100)->count();
     * @return int the count
     * @throws CModelException
     */
    public function count()
    {
        if ($this->pdo == null) {
            throw new CModelException("pdo is null");
        }
        self::$lastQuery = '';
        $query = 'select count(*) as total from ' . $this->table . $this->condition . $this->order . $this->limit;
        self::$lastQuery = $query;
        $sth = $this->pdo->prepare($query);
        $sth->execute();

        $obj = $sth->fetch(\PDO::FETCH_OBJ);
        $this->clearQuery();
        return $obj->total;
    }

    /**
     * sum by condition
     * @param string $column
     * @return int sum the coluun
     * @throws CModelException
     */
    public function sum($column)
    {
        if ($this->pdo == null) {
            throw new CModelException("pdo is null");
        }
        self::$lastQuery = '';
        $query = 'select sum(' . $column . ') as total from ' . $this->table . $this->condition . $this->order . $this->limit;
        self::$lastQuery = $query;
        $sth = $this->pdo->prepare($query);
        $sth->execute();

        $obj = $sth->fetch(\PDO::FETCH_OBJ);
        $this->clearQuery();
        return $obj->total;
    }

    /**
     * get max by condition
     * @param string $column
     * @return int max the column
     * @throws CModelException
     */
    public function max($column)
    {
        if ($this->pdo == null) {
            throw new CModelException("pdo is null");
        }
        self::$lastQuery = '';
        $query = 'select max(' . $column . ') as max from ' . $this->table . $this->condition . $this->order . $this->limit;
        self::$lastQuery = $query;
        $sth = $this->pdo->prepare($query);
        $sth->execute();

        $obj = $sth->fetch(\PDO::FETCH_OBJ);
        $this->clearQuery();
        return $obj->max;
    }

    public function min($column)
    {
        if ($this->pdo == null) {
            throw new CModelException("pdo is null");
        }
        self::$lastQuery = '';

        $query = 'select min(' . $column . ') as min from ' . $this->table . $this->condition . $this->order . $this->limit;
        self::$lastQuery = $query;
        $sth = $this->pdo->prepare($query);
        $sth->execute();

        $obj = $sth->fetch(\PDO::FETCH_OBJ);
        $this->clearQuery();
        return $obj->min;
    }

    public function distinct($column)
    {
        if ($this->pdo == null) {
            throw new CModelException("pdo is null");
        }
        self::$lastQuery = '';
        $query = 'select distinct(' . $column . ') from ' . $this->table . $this->condition;
        self::$lastQuery = $query;
        $sth = $this->pdo->prepare($query);
        $sth->execute();

        $objs = $sth->fetchAll(\PDO::FETCH_OBJ);
        $this->clearQuery();

        $list = array();
        foreach ($objs as $obj) {
            $list[] = $obj->$column;
        }

        return $list;
    }

    /**
     * whereIn('field',[1,3,4]);
     * @param string $column
     * @param array $values
     * @param string $bool
     * @return $this
     * @throws CModelException
     */
    public function whereIn($column, $values, $bool = 'and')
    {
        if (!is_array($values) || count($values) == 0) {
            throw new CModelException("param values is not a array or array is empty");
        }
        if ($this->condition == '') {
            $this->condition = ' where ';
            
            for($i=0;$i<$this->needLeftBracketNum;++$i) {
                $this->condition .= "(";
            }
            $this->needLeftBracketNum = 0;
            
            $this->condition .= "`$column` in ('" . implode("','", $values) . "')";
        } else {
            $this->condition .= " $bool ";
            
            for($i=0;$i<$this->needLeftBracketNum;++$i) {
                $this->condition .= "(";
            }
            $this->needLeftBracketNum = 0;

            $this->condition .= "`$column` in ('" . implode("','", $values) . "')";
        }

        return $this;
    }

    public function whereNotIn($column, $values, $bool = 'and')
    {
        if (!is_array($values) || count($values)== 0) {
            throw new CModelException("param values is not a array or array is empty");
        }
        if ($this->condition == '') {
            $this->condition = ' where ';
            
            for($i=0;$i<$this->needLeftBracketNum;++$i) {
                $this->condition .= "(";
            }
            $this->needLeftBracketNum = 0;
            
            $this->condition .= "`$column` not in ('" . implode("','", $values) . "')";
        } else {

            $this->condition .= " $bool ";
            
            for($i=0;$i<$this->needLeftBracketNum;++$i) {
                $this->condition .= "(";
            }
            $this->needLeftBracketNum = 0;

            $this->condition .= "`$column` not in ('" . implode("','", $values) . "')";
        }
        return $this;
    }

    /**
     * insert obj into mysql
     * @param Object $obj
     * @return lastInsertId
     * @throws CModelException
     */
    public function insert($obj)
    {
        if ($this->pdo == null) {
            throw new CModelException("pdo is null");
        }
        self::$lastQuery = '';
        $query = 'insert into ' . $this->table . $this->getInsertList($obj);
        self::$lastQuery = $query;
        $sth = $this->pdo->prepare($query);
        $sth->execute();

        return $this->pdo->lastInsertId();
    }

    /**
     * set update raw expression
     * @param string $expr
     * @return $this
     */
    public function setRaw($expr)
    {
        if ($this->update == '') {
            $this->update = $expr;
        } else {
            $this->update .= "," . $expr;
        }

        return $this;
    }

    /**
     * update the obj into mysql
     * where('id','=',100)->update($obj);
     * @param Object $obj
     * @return affect rows
     * @throws CModelException
     */
    public function update($obj = [])
    {
        if ($this->pdo == null) {
            throw new CModelException("pdo is null");
        }

        $list = $this->getUpdateList($obj);
        $comma = "";
        if ($list != '' && $this->update != '') {
            $comma = ',';
        }
        self::$lastQuery = '';
        $query = 'update ' . $this->table . ' set ' . $this->update . $comma . $list . $this->condition . $this->limit;
        self::$lastQuery = $query;
        if (trim($this->condition) == '') {
            throw new CModelException("where must be not empty");
        }

        $sth = $this->pdo->prepare($query);
        $sth->execute();
        $this->clearQuery();

        return $sth->rowCount();
    }

    /**
     * update object into mysql by pk,obj need have to pk field
     * @param Object $obj
     * @return affect rows
     * @throws CModelException
     */
    public function updateByPk($obj)
    {
        $obj = (Object)$obj;
        $pk = $this->pk;

        if (!isset($obj->$pk) || is_int($obj->$pk) && $obj->$pk == 0 || is_string($obj->$pk) && $obj->$pk == '') {
            throw new CModelException("primary key is empty");
        }

        return $this->where($pk, '=', $obj->$pk)->update($obj);
    }

    /**
     * save data into mysql,obj need have the pk field
     * @param Object $obj
     * @return affect rows
     * @throws CModelException
     */
    public function saveByPk($obj)
    {
        $obj = (Object)$obj;
        $pk = $this->pk;
        if (!isset($obj->$pk) || is_int($obj->$pk) && $obj->$pk == 0 || is_string($obj->$pk) && $obj->$pk == '') {
            throw new CModelException("primary key is empty");
        }
        $count = $this->where($pk, '=', $obj->$pk)->count();
        if ($count == 0) {
            return $this->insert($obj);
        } else {
            return $this->where($pk, '=', $obj->$pk)->update($obj);
        }
    }

    public function deleteByPk($id)
    {
        return $this->where($this->pk, '=', $id)->delete();
    }

    /**
     * delete data from mysql by condition
     * @return affect rows
     * @throws CModelException
     */
    public function delete()
    {
        if ($this->pdo == null) {
            throw new CModelException("pdo is null");
        }
        self::$lastQuery = '';
        $query = 'delete from ' . $this->table . $this->condition;
        self::$lastQuery = $query;

        if (trim($this->condition) == '') {
            throw new CModelException("where must be not empty");
        }
        $sth = $this->pdo->prepare($query);
        $sth->execute();
        $this->clearQuery();
        return $sth->rowCount();
    }

    public static function isWord($value)
    {
        $pattern1 = "/^\w+$/";
        if (preg_match($pattern1, $value)) {
            return true;
        }
        return false;
    }

    private function getSelectList($columns)
    {
        $list = '';
        foreach ($columns as $column) {
            if ($list == '') {
                $this->isWord($column) ? ($list = "`" . $column . "`") : ($list = $column);
            } else {
                $this->isWord($column) ? ($list .= ",`" . $column . "`") : ($list .= ',' . $column);
            }
        }
        return $list;
    }

    private function getInsertList($obj)
    {
        $keys = '';
        $values = '';
        foreach ($obj as $key => $value) {
            if ($keys == '') {
                $keys = "`$key`";
            } else {
                $keys .= ",`$key`";
            }

            if ($values == '') {
                $values = "'" . addslashes($value) . "'";
            } else {
                $values .= ",'" . addslashes($value) . "'";
            }
        }
        $list = ' (' . $keys . ') values (' . $values . ')';
        return $list;
    }

    private function getUpdateList($obj)
    {
        $list = '';
        foreach ($obj as $key => $value) {
            if ($list == '') {
                $list = "`$key` = '" . addslashes($value) . "'";
            } else {
                $list .= ",`$key` = '" . addslashes($value) . "'";
            }
        }
        return $list;
    }

     /**
     * save data into mysql
     * @param Object $obj
     * @return affect rows
     * @throws CModelException
     */
    public function save($obj)
    {
        $obj = (Object)$obj;

        $where = $this->condition;
        $count = $this->count();
        if ($count > 1) {
            throw new CModelException("save only affect one record");
        }
        if ($count == 0) {
            return $this->insert($obj);
        } else {
            $this->condition = $where;
            return $this->update($obj);
        }
    }
}
