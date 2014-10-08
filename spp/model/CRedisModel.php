<?php
abstract class CRedisModel extends CModel
{
	abstract protected  function redis();
	
	protected  function prefix()
	{
		return strtolower(get_class($this));
	}

	public  function getNKey()
	{
		$keyName = $this->keyName();
	
		if($this->$keyName == null || $this->$keyName == '' || $this->$keyName == 0)
			throw new CModelException('the primay key field '.$keyName.' in '.get_class($this).' not setted');
	
		if($this->prefix() != '')
			return $this->prefix()."_".$this->$keyName;
		else
			return strtolower(get_class($this))."_".$this->$keyName;
	}
	
	public function save()
	{
		$nskey = $this->getNKey();
		$var = $this->toArray();
		$ret = false;
		if($this->isCreate())
		{
			$ret = $this->redis()->setnx($nskey,CUtils::encode($var));
			if(!$ret)
			{
				throw new CModelException($nskey.' add to redis fail in '.get_class($this));
			}
		}
		else 
		{
			$ret = $this->redis()->set($nskey,CUtils::encode($var));
			if(!$ret)
			{
				throw new CModelException($nskey.' save to redis fail in '.get_class($this));
			}
		}
	}

	public function get($key)
	{
		$this->setKey($key);
		$var = $this->redis()->get($this->getNKey());
		
		if($var !== false)
		{
			$this->fromArray(CUtils::decode($var))->setDirty(false)->setCreate(false);
			return $this;
		}
		
		return false;

	}

	public function delete($key)
	{
		$this->setKey($key);
		return $this->redis()->delete($this->getNKey());
	}
	
	public static function mget($keys)
	{
		$caller= get_called_class();
		$callerObj= new $caller();
		
		$objs = array();
		$nsKeys = array();
		
		for($i=0; $i < count($keys); $i++)
		{
			$callerObj->setKey($keys[$i]);
			$nsKeys[$callerObj->getNKey()]=$keys[$i];
		}
		
		$vars = $callerObj->redis()->getMultiple(array_keys($nsKeys));
		
		foreach ($vars as $var)
		{
			if($var !== false)
			{
				$obj=new $caller();
				$obj->fromArray(CUtils::decode($var))->setDirty(false)->setCreate(false);
				$objs[$obj->getKey()] = $obj;
			}
		}
		
		return $objs;
	}
}