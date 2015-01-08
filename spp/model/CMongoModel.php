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
		$key = $this->getKey();
		if($key == '' || $key == null)
		{
			throw new CModelException('primay key field '.$this->keyName().' is not set in '.get_class($this));
		}
		
		$var['_id'] = $key;		
		
		$collection = $this->prefix();
		
		$ret = false;
		if($this->isCreate())
		{
			$ret = $this->mongodb()->$collection->insert($var);
		}
		else
		{
			$ret = $this->mongodb()->$collection->save($var);
		}
		if(!$ret)
		{
			throw new CModelException('save mongodb fail in '.get_class($this));
		}
	}

	public function get($key)
	{
		 $this->setKey($key);
		 $collection = $this->prefix();
		 
		 $rmonodbs = $this->rmongodbs();
		 $var = null;
		 
		 if($rmonodbs!=null && count($rmonodbs) > 0)
		 {
		 	$index = rand() % count($rmonodbs);
		 	$mongodb = $rmonodbs[$index];
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
		 	$this->fromArray($var)->setDirty(false)->setCreate(false);
		 	return $this;
		 }

	}
	
	public static function mget($keys)
	{
		$caller = get_called_class();
		$callerObj =  new $caller();
		
		$objs=array();
		$collection = $callerObj->prefix();
		
		$rmonodbs = $callerObj->rmongodbs();

		$mongodb = null;
		
		if($rmonodbs!=null && count($rmonodbs) > 0)
		{
			$index = rand() % count($rmonodbs);
			$mongodb = $rmonodbs[$index];
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
			$obj->fromArray($result)->setDirty(false)->setCreate(false);
			$objs[$obj->getKey()] = $obj;
		}
		return $objs;
	}

	public function delete($key)
	{
		$collection = $this->prefix();
		return $this->mongodb()->$collection->remove(array('_id' => $key));
	}

	public static function query($where,$fields = null)
	{
		
		$caller = get_called_class();
		$callerObj =  new $caller();
		
		$objs=array();
		$collection = $callerObj->prefix();
		
		$rmonodbs = $callerObj->rmongodbs();
		
		$mongodb = null;
		
		if($rmonodbs!=null && count($rmonodbs) > 0)
		{
			$index = rand() % count($rmonodbs);
			$mongodb = $rmonodbs[$index];
		}
		else
		{
			$mongodb = $callerObj->mongodb();
		}
		
		if(!is_array($fields))
		{
			$results = $mongodb->$collection->find($query);
		}
		else
		{
			$results = $mongodb->$collection->find($query,$fields);
		}
		
		foreach($results as $result)
		{
			$obj=new $caller();
			$obj->setKey($result['_id']);
			$obj->fromArray($result)->setDirty(false)->setCreate(false);
			$objs[$obj->getKey()] = $obj;
		}
		return $objs;
	}
}

