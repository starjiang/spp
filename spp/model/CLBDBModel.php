<?php
abstract class CLBDBModel extends CModel
{
	abstract  protected function pdos();

	protected  function prefix()
	{
		return strtolower(get_class($this));
	}
	
	private function getIdList($keys)
	{
		$newKeys = array();
		foreach ($keys as $key)
		{
			$newKeys[] = "'".$key."'";
		}
		return implode(',', $newKeys);
	}
	
	private  function getIndex($key)
	{
		$fields = $this->fields();
		$defaultValue = $fields[$this->keyName()];
		$index = 0;
		if(is_int($defaultValue))
		{
			$index = (int)$key % count($this->pdos());
		}
		else
		{
			$intKey= crc32($defaultValue);
			$index = $intKey % count($this->pdos());
		}
		return $index;
	}
	
	public function save()
	{
		$pdos = $this->pdos();
		$sth = null;
		if($this->isCreate())
		{
			$sth = $pdos[$this->getIndex($this->getKey())]->prepare('insert into '.$this->prefix().' (id,val) values (:key,:value)');
		}
		else
		{
			$sth = $pdos[$this->getIndex($this->getKey())]->prepare('replace into '.$this->prefix().' (id,val) values (:key,:value)');
		}
		
		if(!$sth)
		{
			$error=$pdos[$this->getIndex($this->getKey())]->errorInfo();
			throw new CModelException($error[2]);
		}
		if($sth->execute(array('key'=>$this->getKey(),'value'=>CUtils::encode($this->toArray()))) === false)
		{
			$error=$sth->errorInfo();
			throw new CModelException($error[2]);
		}
	}
	
	public function delete($key)
	{

		$pdos = $this->pdos();
		
		$sth =$pdos[$this->getIndex($this->getKey())]->prepare('delete from '.$this->prefix().' where id = :key');
		
		if(!$sth)
		{
			$error=$pdos[$this->getIndex($this->getKey())]->errorInfo();
			throw new CModelException($error[2]);
		}
				
		if($sth->execute(array('key'=>$key)) === false)
		{
			$error=$sth->errorInfo();
			throw new CModelException($error[2]);
		}

		return true;
	}
	
	
	public function get($key) 
	{

		$pdos = $this->pdos();
		
		$sth = $pdos[$this->getIndex($this->getKey())]->prepare('select * from '.$this->prefix().' where id = :key');
		
		if(!$sth)
		{
			$error=$pdos[$this->getIndex($this->getKey())]->errorInfo();
			throw new CModelException($error[2]);
		}
		
		if($sth->execute(array('key'=>$key)) === false)
		{
			$error=$sth->errorInfo();
			throw new CModelException($error[2]);
		}
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		
		if(is_array($row))
		{
			$this->fromArray(CUtils::decode($row['val']))->setDirty(false);
			return $this;
		}

		return false;
	}
	
	public static function mget($keys)
	{
	
		$caller = get_called_class();
		$callerObj = new $caller();

		$pdos = $callerObj->pdos();
		
		$indexKeys = array();
		
		foreach($keys as $key)
		{
			$indexKeys[$callerObj->getIndex($key)][]=$key;
		}
		
		$objs = array();
		
		foreach($indexKeys as $index =>$ikeys)
		{
			
			$sth = $pdos[$index]->prepare("select * from ".$callerObj->prefix()." where id in (".$callerObj->getIdList($ikeys).")");
			
			if(!$sth)
			{
				$error=$pdo->errorInfo();
				throw new CModelException($error[2]);
			}
			
			if($sth->execute() === false)
			{
				$error=$sth->errorInfo();
				throw new CModelException($error[2]);
			}
				
			$results = $sth->fetchAll(PDO::FETCH_ASSOC);
						
			if (is_array($results))
			{

				foreach ($results as $result)
				{
					$obj = new $caller();
			
					$obj->fromArray(CUtils::decode($result['val']))->setDirty(false);
						
					$objs[$obj->getKey()] = $obj;
				}
			}
		}
		
		return $objs;
	}	
}

