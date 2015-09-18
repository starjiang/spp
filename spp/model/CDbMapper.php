<?php
namespace spp\model;
use spp\model\CConnMgr;
use spp\model\CMapper;
class CDbMapper implements CMapper
{
	static $op = ['>'=>'>','>='=>'>=','<'=>'<','<='=>'<=',
		'='=>'=','like'=>'like','is'=>'is','is not'=>'is not'
	];
	private static $instances = array();
	private $table = null;
	private $pk = null;
	private $pdo = null;
	private $condition = '';
	private $limit = '';
	private $order = '';
	protected function __construct($table,$pk = 'id') {
		$this->table = $table;
		$this->pk = $pk;
		if(isset(\Config::$db)){
			$this->pdo = CConnMgr::getInstance()->pdo(\Config::$db);
		}
	}
	
	public static function newInstance()
	{
		$caller = get_called_class();
		return new $caller();
	}
	
	private static function checkOperater($op)
	{
		return self::$op[$op] != null;
	}
	
	private static function checkBoolean($bool)
	{
		if($bool == 'and' || $bool == 'or'){
			return true;
		}
		return false;
	}
	
	public static function getInstance($table,$pk = 'id')
	{
		$key = $table.".".$pk;
		if(self::$instances[$key] == null)
		{
			self::$instances[$key] = new self($table,$pk);
		}
		return self::$instances[$key];
	}
	
	function setPdo($pdo)
	{
		$this->pdo = $pdo;
	}
		
	public function findByPk($id,$columns = ['*'])
	{
		if($this->pdo == null){
			throw new CModelException("pdo is null");
		}
		$sth = $this->pdo->prepare('select '.$this->getSelectList($columns).' from '.$this->table.' where `'.$this->pk.'` = :id');
		$sth->execute(array('id'=>$id));
		$obj = $sth->fetch(\PDO::FETCH_OBJ);
		return $obj;
	}
	
	public function where($column,$op,$value,$bool='and')
	{
		var_dump(self::checkOperater($op));
		if(!self::checkOperater($op) || !self::checkBoolean($bool))
		{
			throw new CModelException("invalid op or bool param");
		}
				
		if($this->condition == '')
		{
			$this->condition = " where `$column` $op '".addslashes($value)."'";
		}
		else
		{
			$this->condition .= " $bool `$column` $op '".addslashes($value)."'";
		}
		return $this;
	}
	
	public function limit($count,$offset = 0)
	{
		$this->limit = " limit $offset,$count";
		return $this;
	}
	
	public function orderBy($column,$order = 'desc')
	{
		if($this->order == '')
		{
			$this->order = " order by `$column` $order";
		}
		else
		{
			$this->order .= ",`$column` $order"; 
		}
		return $this;
	}
	
	public function find($columns = ['*'])
	{
		if($this->pdo == null){
			throw new CModelException("pdo is null");
		}
		
		$query = 'select '.$this->getSelectList($columns).' from '.$this->table.  $this->condition.  $this->order.  $this->limit;
		$sth = $this->pdo->prepare($query);
		$sth->execute();
		
		$objs = $sth->fetchAll(\PDO::FETCH_OBJ);
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
		
		return $objs;
	}
	
	public function count()
	{
		if($this->pdo == null){
			throw new CModelException("pdo is null");
		}
		
		$query = 'select count(*) as total from '.$this->table.  $this->condition.  $this->order.  $this->limit;

		$sth = $this->pdo->prepare($query);
		$sth->execute();
		
		$obj = $sth->fetch(\PDO::FETCH_OBJ);
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
		return $obj->total;
	}
	
	public function distinct($column)
	{
		if($this->pdo == null){
			throw new CModelException("pdo is null");
		}
		
		$query = 'select distinct('.$column.') from '.$this->table.  $this->condition;

		$sth = $this->pdo->prepare($query);
		$sth->execute();
		
		$objs = $sth->fetchAll(\PDO::FETCH_OBJ);
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
		
		$list = array();
		foreach ($objs as $obj)
		{
			$list[] = $obj->$column;
		}
		
		return $list;
	}
	
	
	public function whereIn($column,$values,$bool = 'and')
	{
		if(!is_array($values) && count($values))
		{
			throw new CModelException("param values is not a array");
		}
		if($this->condition == '')
		{
			$this->condition = " where `$column` in ('".  implode("','", $values)."')";
		}
		else
		{
			$this->condition .= " $bool `$column` in ('".  implode("','", $values)."')";
		}
		
		return $this;
	}
	
	public function whereNotIn($column,$values,$bool = 'and')
	{
		if(!is_array($values) && count($values))
		{
			throw new CModelException("param values is not a array");
		}
		if($this->condition == '')
		{
			$this->condition = " where `$column` not in (".  implode(",", $values).")";
		}
		else
		{
			$this->condition .= " $bool `$column` not in (".  implode(",", $values).")";
		}
		return $this;
	}
	
	public function insert($obj)
	{
		if($this->pdo == null){
			throw new CModelException("pdo is null");
		}
		$query = 'insert into '.$this->table. $this->getInsertList($obj);
		$sth = $this->pdo->prepare($query);
		$sth->execute();
		return $this->pdo->lastInsertId();
	}
	
	public function update($obj)
	{
		if($this->pdo == null){
			throw new CModelException("pdo is null");
		}
		$query = 'update '.$this->table.' set '. $this->getUpdateList($obj).$this->condition;
		$sth = $this->pdo->prepare($query);
		$sth->execute();
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
	}
	
	public function updateByPk($obj)
	{
		$pk = $this->pk;
		if($obj->$pk == 0 || $obj->$pk == '')
		{
			throw new CModelException("primary key is empty");			
		}
		$this->where($pk,'=',$obj->$pk)->update($obj);
	}
	
	public function save($obj)
	{
		$pk = $this->pk;
		
		if($obj->$pk == 0 || $obj->$pk == '')
		{
			throw new CModelException("primary key is empty");			
		}
		$preObj = $this->findByPk($obj->$pk);		
		if($preObj == false)
		{
			$this->insert($obj);
		}
		else
		{
			$this->where($pk,'=',$obj->$pk)->update($obj);
		}
	}
	public function deleteByPk($id)
	{
		$this->where($this->pk, '=', $id)->delete();
	}
	public function delete()
	{
		if($this->pdo == null){
			throw new CModelException("pdo is null");
		}
		$query = 'delete from '.$this->table.$this->condition;
		$sth = $this->pdo->prepare($query);
		$sth->execute();
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
	}
	private function getSelectList($columns)
	{
		$list = '';
		foreach($columns as $column)
		{
			if($list == '')
			{
				($column != '*') ? ($list = "`".$column."`" ): ($list = $column);
			}
			else 
			{
				($column != '*') ? ($list .= ",`".$column."`" ): ($list .=','.column);
			}
		}
		return $list;
	}
	
	private function getInsertList($obj)
	{
		$keys = '';
		$values = '';
		foreach ($obj as $key=>$value)
		{
			if($keys == '')
			{
				$keys = "`$key`";
			}
			else
			{
				$keys .=",`$key`";
			}
			
			if($values == '')
			{
				$values = "'".addslashes($value)."'";
			}
			else
			{
				$values .=",'".addslashes($value)."'";
			}
		}
	
		$list = ' ('.$keys.') values ('.$values.')';
		
		return $list;
	}
	private function getUpdateList($obj)
	{
		$list = '';
		foreach ($obj as $key=>$value)
		{
			if($list == '')
			{
				$list = "`$key` = '".addslashes($value)."'";
			}
			else
			{
				$list .= ",`$key` = '".addslashes($value)."'";
			}
		}
		return $list;
	}
	
}

