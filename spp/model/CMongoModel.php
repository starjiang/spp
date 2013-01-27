<?php
abstract class CMongoModel extends CModel
{

	abstract protected  function mongodb();
	
	protected  function rmongodbs() { return null; }
	
	protected  function prefix()
	{
		return strtolower(get_class($this));
	}
		
	public  function save()
	{

		$var = $this->toArray();
		
		if($this->keyName() != '_id')
		{
			throw new CModelException('mongodb keyname must be [_id] in '.get_class($this));
		}
		
		
		$collection = $this->prefix();
		
		if(!$this->mongodb()->$collection->save($var))
		{
			throw new CModelException('save mongodb fail in '.get_class($this));
		}
	}

	public function get($key)
	{
		 $this->setKey($key);
		 $collection = $this->prefix();
		 
		 $RMmonodbs = $this->rmongodbs();
		 $var = null;
		 
		 if($RMmonodbs!=null && count($RMmonodbs) > 0)
		 {
		 	$index = rand() % count($RMmonodbs);
		 	$mongodb = $RMmonodbs[$index];
		 	$var = $mongodb->$collection->findOne(array('_id' => $key));
		 }
		 else 
		 {
		 	$var = $this->mongodb()->$collection->findOne(array('_id' => $key));
		 }
		 
		 if( $var === null)
		 {
		 	return false;
		 }
		 else
		 {
		 	$this->fromArray($var)->setDirty(false);
		 	return $this;
		 }

	}
	
	public static function mget($keys)
	{
		$caller = get_called_class();
		$callerObj =  new $caller();
		
		$objs=array();
		$collection = $callerObj->prefix();
		
		$RMmonodbs = $callerObj->rmongodbs();

		$mongodb = null;
		
		if($RMmonodbs!=null && count($RMmonodbs) > 0)
		{
			$index = rand() % count($RMmonodbs);
			$mongodb = $RMmonodbs[$index];
		}
		else
		{
			$mongodb = $callerObj->mongodb();
		}
				
		$results = $mongodb->$collection->find(array('_id' => array('$in' => $keys)));
		
		foreach($results as $result)
		{
			$obj=new $caller();
			$obj->setKey($result['_id']);
			$obj->fromArray($result)->setDirty(false);
			$objs[$result['_id']] = $obj;
		}
		return $objs;
	}

	public function delete($key)
	{
		$collection = $this->prefix();
		return $this->mongodb()->$collection->remove(array('_id' => $key));
	}

	public static function query($where)
	{
		
		$caller = get_called_class();
		$callerObj =  new $caller();
		
		$objs=array();
		$collection = $callerObj->prefix();
		
		$RMmonodbs = $callerObj->rmongodbs();
		
		$mongodb = null;
		
		if($RMmonodbs!=null && count($RMmonodbs) > 0)
		{
			$index = rand() % count($RMmonodbs);
			$mongodb = $RMmonodbs[$index];
		}
		else
		{
			$mongodb = $callerObj->mongodb();
		}
		
		
		$results = $mongodb->$collection->find($where);
		
		foreach($results as $result)
		{
			$obj=new $caller();
			$obj->setKey($result['_id']);
			$obj->fromArray($result)->setDirty(false);
			$objs[$result['_id']] = $obj;
		}
		return $objs;
	}
}

