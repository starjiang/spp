<?php
class CRedisModel extends CModel
{
	abstract protected  function redis();
	
	protected  function prefix()
	{
		return strtolower(get_class($this));
	}

	public  function getKey()
	{
		$keyName = $this->keyName();
	
		if($this->$keyName == null || $this->$keyName == '')
			throw new ErrorException('the primay key field '.$keyName.' in '.get_class($this).' not setted');
	
		if($this->prefix() != '')
			return $this->prefix()."_".$this->$keyName;
		else
			return strtolower(get_class($this))."_".$this->$keyName;
	}
	
	public function save()
	{
		$keyName=$this->keyName();
		$nskey = $this->getKey();
		$var = $this->toArray();
		$collection = $this->prefix();
		return $this->redis()->set($nskey,json_encode($var));

	}

	public function get($key)
	{
		$this->setKey($key);
		$var = $this->redis()->get($this->getKey());
		
		if($var !== false)
		{
			$this->fromArray(json_decode($var,true))->setDirty(false);
			return $this;
		}
		
		return false;

	}

	public function delete($key)
	{
		return $this->redis()->delete($key);
	}
	
	public static function mget($keys)
	{
		$caller= get_called_class();
		$callerObj= new $caller();
		
		$objs = array();
		$nsKeys = array();
		for($i=0; $i < count($keys); $i++)
		{
			$obj=new $caller();
			$obj->setKey($keys[$i]);
			$objs[$keys[$i]] = $obj;
			$nsKeys[$obj->getKey()]=$keys[$i];
		}
		
		$vars = $callerObj->redis()->getMultiple(array_keys($nsKeys));
		
		foreach ($vars as $key =>$var)
		{
			$objs[$nsKeys[$key]]->fromArray(json_decode($var,true))->setDirty(false);
			unset($nsKeys[$key]);
		}
		
		return $objs;
	}
}