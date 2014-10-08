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
	
	private function getFeildsList($hasKey = true)
	{
		$fields = array_keys($this->fields());
		if(!$hasKey)
		{
			unset($fields[$this->keyName()]);
		}
		$list = implode(',',$fields);
		return $list;
	}

	private function getInsertFeildsList($hasKey = true)
	{
		$keys=array();
		$fields = array_keys($this->fields());
		if(!$hasKey)
		{
			unset($fields[$this->keyName()]);
		}
		foreach ($fields as $key)
		{
			$keys[]=':'.$key;
		}
	
		$list = implode(',', $keys);
		return $list;
	}
	
	
	private function getUpdateFieldsList()
	{
		$keys=array();
		$fields = array_keys($this->fields());
		unset($fields[$this->keyName()]);
		foreach ($fields as $key)
		{
			$keys[]=$key.'=:'.$key;
		}
	
		$list = implode(',', $keys);
		return $list;
	}
	
	public function save($update = false)
	{
		$sth = null;
		if($this->isCreate())
		{
			if($this->getKey() == '' || $this->getKey() == 0 || $this->getKey() == null)
			{
				$sth = $this->pdo()->prepare('insert into '.$this->prefix().' ('.$this->getFeildsList(false).') values ('.$this->getInsertFeildsList(false).')');
			}
			else 
			{
				$sth = $this->pdo()->prepare('insert into '.$this->prefix().' ('.$this->getFeildsList().') values ('.$this->getInsertFeildsList().')');
			}
		}
		else 
		{
			if($update)
			{
				$sth = $this->pdo()->prepare('update '.$this->prefix().' set '.$this->getUpdateFieldsList().' where '.$this->keyName().'='.$this->getKey());
			}	
			else 
			{
				$sth = $this->pdo()->prepare('replace into '.$this->prefix().' ('.$this->getFeildsList().') values ('.$this->getInsertFeildsList().')');
			}
		}
		
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
		if($this->isCreate())
		{
			$this->setKey($this->pdo()->lastInsertId());
		}
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
				
		$sth = $pdo->prepare('select '.$this->getFeildsList().' from '.$this->prefix().' where '.$this->keyName().' = :id');
		
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
			$this->fromArray($row)->setDirty(false)->setCreate(false);
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
		
		$sth = $pdo->prepare("select ".$callerObj->getFeildsList()." from ".$callerObj->prefix()." where ".$callerObj->keyName()." in (".$callerObj->getIdList($keys).")");
		
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

				$obj->fromArray($result)->setDirty(false)->setCreate(false);
			
				$objs[$result[$obj->keyName()]] = $obj;
			}
			return $objs;
		}
		return false;
	}

	public static function queryCount($query = '',$params = null)
	{
		$caller = get_called_class();
		$callerObj = new $caller();
		
		if($query != '') $query = " ".$query;
		
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
		
		$fieldList = $callerObj->getFeildsList();

		$sth = $pdo->prepare("select count(*) as total from ".$callerObj->prefix().$query);
		
		if(!$sth)
		{
			$error=$pdo->errorInfo();
			throw new CModelException($error[2]);
		}
		
		if(is_array($params))
		{
			foreach($params as $key => $value)
			{
				if(is_int($value))
				{
					$sth->bindValue(":$key",$value,PDO::PARAM_INT);
				}
				else if(is_string($value))
				{
					$sth->bindValue(":$key",$value,PDO::PARAM_STR);
				}
				else if(is_bool($value))
				{
					$sth->bindValue(":$key",$value,PDO::PARAM_BOOL);
				}
			}
		}

		if($sth->execute() === false)
		{
			$error=$sth->errorInfo();
			throw new CModelException($error[2]);
		}
			
		$result = $sth->fetch(PDO::FETCH_ASSOC);
		return (int)$result['total'];
		
	}

	public static function query($query = '',$params = null,$fields = null)
	{
		$caller = get_called_class();
		$callerObj = new $caller();
		
		if($query != '') $query = " ".$query;
		
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

		$fieldList = $callerObj->getFeildsList();
		if(is_array($fields))
		{
			$fieldList = implode(',', $fields);
		}
		
		$sth = $pdo->prepare("select ".$fieldList." from ".$callerObj->prefix().$query);
		
		if(!$sth)
		{
			$error=$pdo->errorInfo();
			throw new CModelException($error[2]);
		}
		
		if(is_array($params))
		{
			foreach($params as $key => $value)
			{
				if(is_int($value))
				{
					$sth->bindValue(":$key",$value,PDO::PARAM_INT);
				}
				else if(is_string($value))
				{
					$sth->bindValue(":$key",$value,PDO::PARAM_STR);
				}
				else if(is_bool($value))
				{
					$sth->bindValue(":$key",$value,PDO::PARAM_BOOL);
				}
			}
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
		
				$obj->fromArray($result)->setDirty(false)->setCreate(false);
					
				$objs[$obj->getKey()] = $obj;
			}
			return $objs;
		}
		return false;
	}
}

