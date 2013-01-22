<?php
abstract class CDBModel extends CModel
{
	abstract  protected function pdo();
	protected function rpdos(){return null;}
	
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
	
	private function getFeildsList1()
	{
		
		$list = implode(',', array_keys($this->fields()));
		return $list;
	}
	
	private function getFeildsList2()
	{
		$keys=array();
		$fields = array_keys($this->fields());
		foreach ($fields as $key)
		{
			$keys[]=':'.$key;
		}
		$list = implode(',', $keys);
		return $list;
	}
	
	public function save()
	{
		$sth = $this->pdo()->prepare('replace into '.$this->prefix().' ('.$this->getFeildsList1().') values ('.$this->getFeildsList2().')');
		
		if(!$sth)
		{
			$error=$this->pdo()->errorInfo();
			throw new CModelException($error[2]);
		}
		
		if($sth->execute($this->toArray()) === false)
		{
				
			$error=$sth->errorInfo();
			throw new CModelException($error[2]);
		}
		return true;
	}
	
	public function delete($key)
	{
		$sth = $this->pdo()->prepare('delete from '.$this->prefix().' where '.$this->keyName().' = :id');
		
		if(!$sth)
		{
			$error=$this->pdo()->errorInfo();
			throw new CModelException($error[2]);
		}
				
		if($sth->execute(array('id'=>$key)) === false)
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
				
		$sth = $pdo->prepare('select * from '.$this->prefix().' where '.$this->keyName().' = :id');
		
		if(!$sth)
		{
			$error=$pdo->errorInfo();
			throw new CModelException($error[2]);
		}
		
		if($sth->execute(array('id'=>$key)) === false)
		{
			$error=$sth->errorInfo();
			throw new CModelException($error[2]);
		}
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		
		if(is_array($row))
		{
			$this->fromArray($row)->setDirty(false);
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
		
		$sth = $pdo->prepare("select * from ".$callerObj->prefix()." where ".$callerObj->keyName()." in (".$callerObj->getIdList($keys).")");
		
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

				$obj->fromArray($result)->setDirty(false);
			
				$objs[$result[$obj->keyName()]] = $obj;
			}
			return $objs;
		}
		return false;
	}
		
	public static function query($where = '')
	{
		$caller = get_called_class();
		$callerObj = new $caller();
		
		if($where != '') $where = " where ".$where;
		
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

		
		$sth = $pdo->prepare("select * from ".$callerObj->prefix().$where);
		
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
		
				$obj->fromArray($result)->setDirty(false);
					
				$objs[$result[$obj->keyName()]] = $obj;
			}
			return $objs;
		}
		return false;
	}
}

