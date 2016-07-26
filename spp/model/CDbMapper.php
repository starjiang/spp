<?php

namespace spp\model;

use spp\model\CConnMgr;
use spp\model\CMapper;

class CDbMapper implements CMapper {

	static $op = ['>' => '>', '>=' => '>=', '<' => '<', '<=' => '<=',
		'=' => '=', '!=' => '!=', 'like' => 'like', 'is' => 'is', 'is not' => 'is not'
	];
	const CHECK_LENTH = 50;
	private static $instances = array();
	private static $lastQuery = '';
	private $table = null;
	private $pk = null;
	private $pdo = null;
	private $condition = '';
	private $limit = '';
	private $order = '';
	private $needLeftBracket = false;

	protected function __construct($table, $pk = 'id') {
		$this->table = "`" . $table . "`";
		$this->pk = $pk;
		if (isset(\Config::$db)) {
			$this->pdo = CConnMgr::getInstance()->pdo(\Config::$db);
		}
	}

	public static function newInstance() {
		$caller = get_called_class();
		return new $caller();
	}

	public static function getLastQuery(){
		return self::$lastQuery;
	}
	
	private static function checkOperater($op) {
		return self::$op[$op] != null;
	}

	private static function checkBoolean($bool) {
		if ($bool == 'and' || $bool == 'or') {
			return true;
		}
		return false;
	}

	public static function getInstance($table, $pk = 'id') {
		$key = $table . "." . $pk;
		if (self::$instances[$key] == null) {
			self::$instances[$key] = new self($table, $pk);
		}
		return self::$instances[$key];
	}

	function setPdo($pdo) {
		$this->pdo = $pdo;
	}

	public function findByPk($id, $columns = ['*']) {
		if ($this->pdo == null) {
			throw new CModelException("pdo is null");
		}
		$sth = $this->pdo->prepare('select ' . $this->getSelectList($columns) . ' from ' . $this->table . ' where `' . $this->pk . '` = :id');
		$sth->execute(array('id' => $id));
		$obj = $sth->fetch(\PDO::FETCH_OBJ);
		return $obj;
	}
	
	public static function haveExpress($value) {
		$pattern1 = "/^\w+$/";
		$pattern2 = "/^(\w+\(\w*\)[\+\-\*\/]\w+)$|^(\w+\(\w*\(\w*\)\)[\+\-\*\/]\w+)$|^(\w+[\+\-\*\/]\w+)$|^(\w+\(\w*\))$|^(\w+[\+\-\*\/]\w+\(\w*\))$|^(\w+[\+\-\*\/]\w+\(\w+\(\w*\)\))$|^(\w+\(\w+\(\w*\)\))$|^(\w+\(\w*\)[\+\-\*\/]\w+\(\w*\))$/";
		
		if(strlen($value) > self::CHECK_LENTH ) {
			return false;
		} 
		
		if(preg_match($pattern1, $value)) {
			return false;
		}
		
		if(preg_match($pattern2, $value)) {
			return true;
		} else {
			return false;
		}
	}

	public function where($column, $op, $value, $bool = 'and') {
		if (!self::checkOperater($op) || !self::checkBoolean($bool)) {
			throw new CModelException("invalid op or bool param");
		}

		if ($this->condition == '') {
			if (!self::haveExpress($column)) {
				 $column = "`$column`";
			}
			if(!self::haveExpress($value) || $op == 'like'){
				$value = "'".addslashes($value)."'";
			}
			$this->condition = ' where ';
			if($this->needLeftBracket){
				$this->condition.= "(";
				$this->needLeftBracket = false;
			}
			
			$this->condition.= "$column $op $value";

		} else {
			
			if (!self::haveExpress($column)) {
				 $column = "`$column`";
			}

			if(!self::haveExpress($value) || $op == 'like'){
				$value = "'".addslashes($value)."'";
			}

			$this->condition.=" $bool ";
			if($this->needLeftBracket){
				$this->condition.= "(";
				$this->needLeftBracket = false;
			}
			$this->condition .= "$column $op $value";
		}
		return $this;
	}
	
	public function addLeft(){
		$this->needLeftBracket = true;
		return $this;
	}

	public function addRight(){

		$this->condition .= ')';
		return $this;
	}

	public function limit($count, $offset = 0) {
		$this->limit = " limit $offset,$count";
		return $this;
	}

	public function orderBy($column, $order = 'desc') {
		if ($this->order == '') {
			$this->order = " order by `$column` $order";
		} else {
			$this->order .= ",`$column` $order";
		}
		return $this;
	}

	public function findOne($columns = ['*']) {
		if ($this->pdo == null) {
			throw new CModelException("pdo is null");
		}
		$this->limit(1);
		$query = 'select ' . $this->getSelectList($columns) . ' from ' . $this->table . $this->condition . $this->order . $this->limit;
		self::$lastQuery = $query;
		$sth = $this->pdo->prepare($query);
		$sth->execute();

		$obj = $sth->fetch(\PDO::FETCH_OBJ);
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
		self::$lastQuery = '';
		return $obj;
	}

	public function find($columns = ['*']) {
		
		if ($this->pdo == null) {
			throw new CModelException("pdo is null");
		}
		$query = 'select ' . $this->getSelectList($columns) . ' from ' . $this->table . $this->condition . $this->order . $this->limit;
		self::$lastQuery = $query;
		$sth = $this->pdo->prepare($query);
		$sth->execute();
		$objs = $sth->fetchAll(\PDO::FETCH_OBJ);
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
		self::$lastQuery = '';
		return $objs;
	}
	
	public function count() {
		if ($this->pdo == null) {
			throw new CModelException("pdo is null");
		}

		$query = 'select count(*) as total from ' . $this->table . $this->condition . $this->order . $this->limit;
		self::$lastQuery = $query;
		$sth = $this->pdo->prepare($query);
		$sth->execute();

		$obj = $sth->fetch(\PDO::FETCH_OBJ);
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
		self::$lastQuery = '';
		return $obj->total;
	}


	public function sum($column) {
		if ($this->pdo == null) {
			throw new CModelException("pdo is null");
		}

		$query = 'select sum('.$column.') as total from ' . $this->table . $this->condition . $this->order . $this->limit;
		self::$lastQuery = $query;
		$sth = $this->pdo->prepare($query);
		$sth->execute();

		$obj = $sth->fetch(\PDO::FETCH_OBJ);
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
		self::$lastQuery = '';
		return $obj->total;
	}


	public function max($column) {
		if ($this->pdo == null) {
			throw new CModelException("pdo is null");
		}

		$query = 'select max(' . $column . ') as max from ' . $this->table . $this->condition . $this->order . $this->limit;
		self::$lastQuery = $query;
		$sth = $this->pdo->prepare($query);
		$sth->execute();

		$obj = $sth->fetch(\PDO::FETCH_OBJ);
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
		self::$lastQuery = '';
		return $obj->max;
	}

	public function min($column) {
		if ($this->pdo == null) {
			throw new CModelException("pdo is null");
		}

		$query = 'select min(' . $column . ') as min from ' . $this->table . $this->condition . $this->order . $this->limit;
		self::$lastQuery = $query;
		$sth = $this->pdo->prepare($query);
		$sth->execute();

		$obj = $sth->fetch(\PDO::FETCH_OBJ);
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
		self::$lastQuery = '';
		return $obj->min;
	}

	public function distinct($column) {
		if ($this->pdo == null) {
			throw new CModelException("pdo is null");
		}

		$query = 'select distinct(' . $column . ') from ' . $this->table . $this->condition;
		self::$lastQuery = $query;
		$sth = $this->pdo->prepare($query);
		$sth->execute();

		$objs = $sth->fetchAll(\PDO::FETCH_OBJ);
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
		self::$lastQuery = '';
		$list = array();
		foreach ($objs as $obj) {
			$list[] = $obj->$column;
		}

		return $list;
	}

	public function whereIn($column, $values, $bool = 'and') {
		if (!is_array($values) && count($values)) {
			throw new CModelException("param values is not a array");
		}
		if ($this->condition == '') {
			$this->condition = ' where ';
			if($this->needLeftBracket){
				$this->condition.= "(";
				$this->needLeftBracket = false;
			}
			$this->condition .= "`$column` in ('" . implode("','", $values) . "')";
		} else {
			$this->condition.=" $bool ";
			if($this->needLeftBracket){
				$this->condition.= "(";
				$this->needLeftBracket = false;
			}
			
			$this->condition .= "`$column` in ('" . implode("','", $values) . "')";
		}
		return $this;
	}

	public function whereNotIn($column, $values, $bool = 'and') {
		if (!is_array($values) && count($values)) {
			throw new CModelException("param values is not a array");
		}
		if ($this->condition == '') {
			$this->condition = ' where ';
			if($this->needLeftBracket){
				$this->condition.= "(";
				$this->needLeftBracket = false;
			}
			$this->condition .= "`$column` not in (" . implode(",", $values) . ")";
		} else {
			
			$this->condition.=" $bool ";
			if($this->needLeftBracket){
				$this->condition.= "(";
				$this->needLeftBracket = false;
			}
			
			$this->condition .= "`$column` not in (" . implode(",", $values) . ")";
		}
		return $this;
	}

	public function insert($obj) {
		if ($this->pdo == null) {
			throw new CModelException("pdo is null");
		}
		$query = 'insert into ' . $this->table . $this->getInsertList($obj);
		self::$lastQuery = $query;
		$sth = $this->pdo->prepare($query);
		$sth->execute();
		self::$lastQuery = '';
		return $this->pdo->lastInsertId();
	}
	
	public function update($obj)
	{

		if ($this->pdo == null) {
			throw new CModelException("pdo is null");
		}
		
		$query = 'update ' . $this->table . ' set ' . $this->getUpdateList($obj) . $this->condition;
		self::$lastQuery = $query;
		
		if(trim($this->condition) == '') {
			throw new CModelException("where must be not empty");
		}
		
		$sth = $this->pdo->prepare($query);
		$sth->execute();
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
		self::$lastQuery = '';
	}

	public function updateByPk($obj) {
		$obj = (Object)$obj;
		$pk = $this->pk;
		if (!isset($obj->$pk) || is_int($obj->$pk) && $obj->$pk == 0 || is_string($obj->$pk) && $obj->$pk == '') {
			throw new CModelException("primary key is empty");
		}
		$this->where($pk, '=', $obj->$pk)->update($obj);
	}

	public function saveByPk($obj) {
		$obj = (Object)$obj;
		$pk = $this->pk;
		if (!isset($obj->$pk) || is_int($obj->$pk) && $obj->$pk == 0 || is_string($obj->$pk) && $obj->$pk == '') {
			throw new CModelException("primary key is empty");
		}
		$count = $this->where($pk, '=', $obj->$pk)->count();
		if ($count == 0) {
			$this->insert($obj);
		} else {
			$this->where($pk, '=', $obj->$pk)->update($obj);
		}
	}

	public function deleteByPk($id) {
		$this->where($this->pk, '=', $id)->delete();
	}

	public function delete() {
		if ($this->pdo == null) {
			throw new CModelException("pdo is null");
		}
		
		$query = 'delete from ' . $this->table . $this->condition;
		self::$lastQuery = $query;
		
		if(trim($this->condition) == '') {
			throw new CModelException("where must be not empty");
		}
		$sth = $this->pdo->prepare($query);
		$sth->execute();
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
		self::$lastQuery = '';
	}

	private function getSelectList($columns) {
		$list = '';
		foreach ($columns as $column) {
			if ($list == '') {
				($column != '*') ? ($list = "`" . $column . "`" ) : ($list = $column);
			} else {
				($column != '*') ? ($list .= ",`" . $column . "`" ) : ($list .=',' . $column);
			}
		}
		return $list;
	}

	private function getInsertList($obj) {
		$keys = '';
		$values = '';
		foreach ($obj as $key => $value) {
			if ($keys == '') {
				$keys = "`$key`";
			} else {
				$keys .=",`$key`";
			}

			if ($values == '') {
				$values = "'" . addslashes($value) . "'";
			} else {
				$values .=",'" . addslashes($value) . "'";
			}
		}
		$list = ' (' . $keys . ') values (' . $values . ')';
		return $list;
	}
	private function getUpdateList($obj) {
		$list = '';
		foreach ($obj as $key => $value) {
			if ($list == '') {
				if(self::haveExpress($value)) {
					$list = "`$key` = " . addslashes($value);
				}else {
					$list = "`$key` = '" . addslashes($value) . "'";
				}
			} else{
				if(self::haveExpress($value)) {
					$list .= ",`$key` = " . addslashes($value);
				} else {
					$list .= ",`$key` = '" . addslashes($value) . "'";
				}
			}
		}
		return $list;
	}
	
	public function save($obj) {
		$obj = (Object)$obj;
		$where = $this->condition;
		$count = $this->count();
		if($count > 1) {
			throw new CModelException("save only affect one record");
		}
		if ($count == 0) {
			$this->insert($obj);
		} else {
			$this->condition = $where;
			$this->update($obj);
		}
	}
}
