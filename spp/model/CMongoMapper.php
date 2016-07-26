<?php
namespace spp\model;
use spp\model\CConnMgr;
use spp\model\CMapper;

class CMongoMapper implements CMapper
{
	static $op = ['>'=>'$gt','>='=>'$gte','<'=>'$lt','<='=>'$lte',
		'='=>'=','!='=>'$ne','like'=>'like','in'=>'$in','not in'=>'$nin'
	];
	private static $instances = array();
	private $collection = null;
	private $mongo = null;
	private $condition = [];
	private $count = 0;
	private $offset = 0;
	private $order = [];
	private $pk = '_id';
	protected function __construct($collection) {
		$this->collection = $collection;
		if(isset(\Config::$mongo)){
			$this->mongo = CConnMgr::getInstance()->mongo(\Config::$mongo);
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
	
	private static function getOperater($op)
	{
		return self::$op[$op];
	}
	private static function checkBoolean($bool)
	{
		if($bool == 'and' || $bool == 'or'){
			return true;
		}
		return false;
	}
	
	public static function getInstance($collection)
	{
		$key = $collection;
		if(self::$instances[$key] == null)
		{
			self::$instances[$key] = new self($collection);
		}
		return self::$instances[$key];
	}
	
	function setMongo($mongo)
	{
		$this->mongo = $mongo;
		
	}
		
	public function findByPk($id,$columns = [])
	{
		if($this->mongo == null){
			throw new CModelException("mongo is null");
		}
		
		$collection = $this->collection;
		$obj = $this->mongo->$collection->findOne(array('_id' => $id),$columns);
		
		if(is_array($obj))
		{
			$obj = (Object)$obj;
		}
		return $obj;
	}
	
	public function where($column,$op,$value,$bool='and')
	{
		if(!self::checkOperater($op) || !self::checkBoolean($bool))
		{
			throw new CModelException("invalid op or bool param");
		}
		
		$op = self::getOperater($op);
		
		if(count($this->condition) == 0)
		{
			if($op == '=')	{
				$this->condition = [$column=>$value];
			}
			else if($op == 'like')
			{
				$this->condition = [$column=>'/'.$value.'/'];
			}
			else {
				$this->condition = [$column => [$op=>$value]];
			}
		}
		else
		{
			if($bool == 'and')
			{
				if($op == '='){
					$this->condition[$column] = $value;
				}
				else
				{
					$this->condition[$column][$op] = $value;
				}
			}
			else
			{
				$condition = $this->condition;
				$this->condition = [];
				$this->condition['$or'][] = [$column=>$value];
				$this->condition['$or'][] = $condition;
			}
		}
		return $this;
	}
	
	public function limit($count,$offset = 0)
	{
		$this->count = $count;
		$this->offset = $offset;
		return $this;
	}
	
	public function orderBy($column,$order = 'desc')
	{
		if(count($this->order) == 0)
		{
			if($order == 'desc')
				$this->order = [$column=>-1];
			else 
				$this->order = [$column=>1];
		}
		else
		{
			if($order == 'desc')
				$this->order[$column] = -1;
			else
				$this->order[$column] = 1;
		}
		return $this;
	}
	
	public function find($columns = [])
	{
		if($this->mongo == null){
			throw new CModelException("mongo is null");
		}
		$collection = $this->collection;
	
		$cursors = null;

		if($this->count!=0 && $this->offset != 0)
		{
			$cursors = $this->mongo->$collection->find($this->condition,$columns)->sort($this->order)->limit($this->count)->skip($this->offset);
		}
		else if($this->count !=0 && $this->offset == 0)
		{
			$cursors = $this->mongo->$collection->find($this->condition,$columns)->sort($this->order)->limit($this->count);
		}
		else if($this->count ==0 && $this->offset != 0)
		{
			$cursors = $this->mongo->$collection->find($this->condition,$columns)->sort($this->order)->skip($this->offset);
		}
		else
		{
			$cursors = $this->mongo->$collection->find($this->condition,$columns)->sort($this->order);
		}
		
		$rows = [];
		foreach ($cursors as $row)
		{
			$rows[] = (Object)$row;
		}
		
		$this->condition = [];
		$this->order = [];
		$this->count = 0;
		$this->offset = 0;
		
		return $rows;
	}
	
	public function findOne($columns = []) {
		if($this->mongo == null){
			throw new CModelException("mongo is null");
		}
		$collection = $this->collection;
	
		$obj = null;

		if($this->count!=0 && $this->offset != 0)
		{
			$obj = $this->mongo->$collection->findOne($this->condition,$columns)->sort($this->order)->limit($this->count)->skip($this->offset);
		}
		else if($this->count !=0 && $this->offset == 0)
		{
			$obj = $this->mongo->$collection->findOne($this->condition,$columns)->sort($this->order)->limit($this->count);
		}
		else if($this->count ==0 && $this->offset != 0)
		{
			$obj = $this->mongo->$collection->findOne($this->condition,$columns)->sort($this->order)->skip($this->offset);
		}
		else
		{
			$obj = $this->mongo->$collection->findOne($this->condition,$columns)->sort($this->order);
		}
		
		$obj = (Object) $obj;
		
		$this->condition = [];
		$this->order = [];
		$this->count = 0;
		$this->offset = 0;
		
		return $obj;
	}


	public function count()
	{
		if($this->mongo == null){
			throw new CModelException("mongo is null");
		}
		
		$collection = $this->collection;
		
		return $this->mongo->$collection->find($this->condition)->count();
	}

	public function sum($column)
	{
		if($this->mongo == null){
			throw new CModelException("mongo is null");
		}
		return 0;	
	}


	public function min($column)
	{
		$obj = $this->orderBy($column,'asc')->findOne([$column]);
		if($obj == null)
		{
			return null;
		}
		else
		{
			return $obj->$column;
		}
	}
	
	public function max($column)
	{
		$obj = $this->orderBy($column)->findOne([$column]);
		if($obj == null)
		{
			return null;
		}
		else
		{
			return $obj->$column;
		}
	}
	
	public function distinct($column)
	{
		if($this->mongo == null){
			throw new CModelException("mongo is null");
		}
		
		$collection = $this->collection;
		return $this->mongo->$collection->distinct($column,  $this->condition);
	}
	
	
	public function whereIn($column,$values,$bool = 'and')
	{
		if(!is_array($values) && count($values))
		{
			throw new CModelException("param values is not a array");
		}
		return $this->where($column, 'in', $value,$bool);
	}
	
	public function whereNotIn($column,$values,$bool = 'and')
	{
		if(!is_array($values) && count($values))
		{
			throw new CModelException("param values is not a array");
		}
		return $this->where($column, 'not in', $value,$bool);
	}
	
	public function insert($obj)
	{
		if($this->mongo == null){
			throw new CModelException("mongo is null");
		}
		
		$collection = $this->collection;
		$this->mongo->$collection->insert($obj);
		return $obj->_id;
	}
	
	public function update($obj)
	{
		if($this->mongo == null){
			throw new CModelException("mongo is null");
		}
		
		$collection = $this->collection; 
		
		$this->mongo->$collection->update($this->condition,$obj);
		
		$this->condition = [];
		$this->order = [];
		$this->count = 0;
		$this->offset = 0;
	}
	
	public function updateByPk($obj)
	{
		$obj = (Object)$obj;
		$pk = $this->pk;
		if($obj->$pk == 0 || $obj->$pk == '')
		{
			throw new CModelException("primary key is empty");			
		}
		$this->where($pk,'=',$obj->$pk)->update($obj);
	}
	
	public function saveByPk($obj)
	{
		$obj = (Object)$obj;
		$pk = $this->pk;
		
		if($obj->$pk == 0 || $obj->$pk == '')
		{
			throw new CModelException("primary key is empty");			
		}
		$count = $this->where($pk,'=',$obj->$pk)->count();		
		if($count == 0)
		{
			$this->insert($obj);
		}
		else
		{
			$this->where($pk,'=',$obj->$pk)->update($obj);
		}
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
	
	public function deleteByPk($id)
	{
		$this->where($this->pk, '=', $id)->delete();
	}
	
	public function delete()
	{
		if($this->mongo == null){
			throw new CModelException("mongo is null");
		}
		
		$collection = $this->collection; 
		$this->mongo->$collection->remove($this->condition);
		$this->condition = [];
		$this->order = [];
		$this->count = 0;
		$this->offset = 0;
	}
	
}

