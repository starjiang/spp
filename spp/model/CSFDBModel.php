<?php
abstract class CSFDBModel extends CModel
{
	abstract  protected function pdo();
	protected function rpdos(){	return null;}
	
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
	
	public function save()
	{
		$sth = $this->pdo()->prepare('replace into '.$this->prefix().'(id,val) values (:key,:value);');
		
		if(!$sth)
		{
			$error=$this->pdo()->errorInfo();
			throw new CModelException($error[2]);
		}
		if($sth->execute(array('key'=>$this->getKey(),'value'=>json_encode($this->toArray()))) === false)
		{
			$error=$sth->errorInfo();
			throw new CModelException($error[2]);
		}
	}
	
	public function delete($key)
	{
		$sth = $this->pdo()->prepare('delete from '.$this->prefix().' where id = :key');
			
		if(!$sth)
		{
			$error=$this->pdo()->errorInfo();
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
		$pdo = null;
		$rpdos = $this->rpdos();
		
		if($rpdos != null && count($rpdos) > 0)
		{
			$index = rand()%count($rpdos);
			$pdo = $rpdos[$index];
		}
		else 
		{
			$pdo = $this->pdo();
		}
		
		$sth = $pdo->prepare('select * from '.$this->prefix().' where id = :key');
			
		if(!$sth)
		{
			$error=$pdo->errorInfo();
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
			$this->fromArray(json_decode($row['val'],true))->setDirty(false);
			return $this;
		}
	
		return false;
	}
	
	public static function mget($keys)
	{
	
		$caller = get_called_class();
		$callerObj = new $caller();
		
		$pdo = null;
		$rpdos = $callerObj->rpdos();
		
		if($rpdos != null && count($rpdos) > 0)
		{
			$index = rand()%count($rpdos);
			$pdo = $rpdos[$index];
		}
		else
		{
			$pdo = $callerObj->pdo();
		}
		
		$sth = $pdo->prepare("select * from ".$callerObj->prefix()." where id in (".$callerObj->getIdList($keys).")");
		
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
			$objs = array();
			foreach ($results as $result)
			{
				$obj = new $caller();

				$obj->fromArray(json_decode($result['val'],true))->setDirty(false);
			
				$objs[$obj->getKey()] = $obj;
			}
			return $objs;
		}
		return false;
	}

}

