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

    protected function __construct($table, $pk = 'id')
    {
        $this->table = "`" . $table . "`";
        $this->pk = $pk;
        if (isset(\Config::$db)) {
            $this->pdo = CConnMgr::getInstance()->pdo(\Config::$db);
        }
    }

    public static function newInstance()
    {
        $caller = get_called_class();
        return new $caller();
    }

    public static function getLastQuery()
    {
        return self::$lastQuery;
    }

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

    public static function getInstance($table, $pk = 'id')
    {
        $key = $table . "." . $pk;
        if (self::$instances[$key] == null) {
            self::$instances[$key] = new self($table, $pk);
        }
        return self::$instances[$key];
    }

    function setPdo($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByPk($id, $columns = ['*'])
    {
        if ($this->pdo == null) {
            throw new CModelException("pdo is null");
        }

        return $this->where($this->pk, '=', $id)->findOne($columns);
    }

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

    public function addLeft()
    {
        $this->needLeftBracketNum++;
        return $this;
    }

    public function addRight()
    {
        $this->condition .= ')';
        return $this;
    }

    public function limit($count, $offset = 0)
    {
        $this->limit = " limit $offset,$count";
        return $this;
    }

    public function updateLimit($count){
        $this->limit = " limit $count";
        return $this;
    }

    public function groupBy($field)
    {
        $this->group = " group by $field";
        return $this;
    }

    public function orderBy($column, $order = 'desc', $rand = false)
    {
        if ($this->order == '') {
            $this->order = " order by `$column` $order";
        } else {
            $this->order .= ",`$column` $order";
        }

        return $this;
    }

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

    public function findOne($columns = ['*'])
    {
        if ($this->pdo == null) {
            throw new CModelException("pdo is null");
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

    public function find($columns = ['*'])
    {
        if ($this->pdo == null) {
            throw new CModelException("pdo is null");
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

    public function whereIn($column, $values, $bool = 'and')
    {
        if (!is_array($values) && count($values)) {
            throw new CModelException("param values is not a array");
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
        if (!is_array($values) && count($values)) {
            throw new CModelException("param values is not a array");
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

    public function setRaw($expr)
    {
        if ($this->update == '') {
            $this->update = $expr;
        } else {
            $this->update .= "," . $expr;
        }

        return $this;
    }

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

    public function updateByPk($obj)
    {
        $obj = (Object)$obj;
        $pk = $this->pk;

        if (!isset($obj->$pk) || is_int($obj->$pk) && $obj->$pk == 0 || is_string($obj->$pk) && $obj->$pk == '') {
            throw new CModelException("primary key is empty");
        }

        return $this->where($pk, '=', $obj->$pk)->update($obj);
    }

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
