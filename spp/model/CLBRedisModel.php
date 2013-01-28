<?php
abstract class CLBRedisModel extends CModel
{

	abstract protected  function rediss();
	
	protected  function prefix()
	{
		return strtolower(get_class($this));
	}
	
	public  function getNKey()
	{
		$keyName = $this->keyName();
	
		if($this->$keyName == null || $this->$keyName == '')
			throw new CModelException('the primay key field '.$keyName.' in '.get_class($this).' not setted');
	
		if($this->prefix() != '')
			return $this->prefix()."_".$this->$keyName;
		else
			return strtolower(get_class($this))."_".$this->$keyName;
	}
		
	private  function getIndex($key)
	{
		$fields = $this->fields();
		$defaultValue = $fields[$this->keyName()];
		$index = 0;
		if(is_int($defaultValue))
		{
			$index = (int)$key % count($this->rediss());
		}
		else
		{
			$intKey= crc32($defaultValue);
			$index = $intKey % count($this->rediss());
		}
		return $index;
	}
	
	public  function save()
	{
		$nskey = $this->getNKey();
		$var = $this->toArray();
		$rediss = $this->rediss();
		
		if(!$rediss[$this->getIndex($this->getKey())]->set($nskey,json_encode($var)))
		{
			throw new CModelException('save redis fail in '.get_class($this));
		}
	}

	public function get($key)
	{

		 $rediss = $this->rediss();

		 $this->setKey($key);
		 $var = $rediss[$this->getIndex($key)]->get($this->getNKey());
		 
		 if( $var === false)
		 {
		 	return false;
		 }
		 else
		 {
		 	$this->fromArray(json_decode($var,true))->setDirty(false);
		 	return $this;
		 }

	}
	
	public static function mget($keys)
	{
		$caller = get_called_class();
		$callerObj =  new $caller();
		
		$rediss = $callerObj->rediss();
		
		$indexKeys = array();
		
		foreach($keys as $key)
		{
			$callerObj->setKey($key);
			$nsKey = $callerObj->getNKey();
			$indexKeys[$callerObj->getIndex($key)][]=$nsKey;
		}
		
		$objs = array();

		foreach($indexKeys as $index =>$ikeys)
		{
			$vars = $rediss[$index]->getMultiple($ikeys);
			
			foreach($vars as $var)
			{
				if($var !== false)
				{
					$obj=new $caller();
					$obj->fromArray(json_decode($var,true))->setDirty(false);
					$objs[$obj->getKey()] = $obj;
				}
			}
		}
		return $objs;
	}

	public function delete($key)
	{
		$rediss = $this->rediss();
		$this->setKey($key);
		return $rediss[$this->getIndex($key)]->delete($this->getNKey());
	}

}

