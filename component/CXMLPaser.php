<?php

class CXMLPaser
{
	private $shmHashMap = null;
	private $data = array();
	private $xml = null;
	private $buckets = 12281 ; 
	private $childnum = 0 ;
	
	/*
	 2909,     3881,     5179,
     6907,     9209,    12281,    
     16381,    21841,    29123,    
     38833,    51787,     69061
    */
	
	public function init($file)
	{
		$this->xml = simplexml_load_file($file);

		if($this->xml === false)
		{
			return false;
		}
		
		return true;
	}
	
	private function countChild($e)
	{
		if($e->count())
		{
			$this->childnum += $e->count(); 
			foreach($e as $child)
			{
				$this->countChild($child);
			}
		}
	}
	
	
	public function toShm($shmKey,$size = 10000000)
	{
		if($this->shmHashMap === null)
		{
			$this->shmHashMap = new CShmHashMap();
		}
		if(!$this->shmHashMap->create($shmKey,$this->buckets,$size))
		{
			return false;
		}
		
		$this->decode($this->xml,true);
		
		return $this->shmHashMap;
	}
	
	public function toArray()
	{
		$this->decode($this->xml,false);
		return $this->data;
	}
	
	private function decode($e,$shmFlag=true,$nameSpace='')
	{
		if($nameSpace == '')
		{
			$nameSpace=$e->getName();	
		}
		else
		{
			$nameSpace.=".".$e->getName();
		}
		
		if($e->count())
		{
			
			if($shmFlag)
			{
				
				$childs = array();
				foreach($e as $child)
				{
					$childs[] = $nameSpace.".".$child->getName();
					$this->decode($child,$shmFlag,$nameSpace);
				}
				$this->shmHashMap->set($nameSpace,$childs);
				
			}
			else
			{
				$this->data[$nameSpace] = array();
				foreach($e as $child)
				{
					$this->data[$nameSpace][] = $nameSpace.".".$child->getName();
					$this->decode($child,$shmFlag,$nameSpace);
				}
			}
		}
		else
		{

			if(count($e->attributes()))
			{
				if($shmFlag)
				{
					$data = $e->attributes();
					$info =array();
					foreach($data as $key =>$value)
					{
						$info[(string)$key] = (string)$value;
					}
					$this->shmHashMap->set($nameSpace,$info);
				}
				else
				{
					$data = $e->attributes();
					$info = array();
					foreach($data as $key =>$value)
					{
						$info[(string)$key] = (string)$value;
					}
					$this->data[$nameSpace] = $info;
				}

			}
			else
			{
				if($shmFlag)
				{
					$this->shmHashMap->set($nameSpace,(string)$e);
				}
				else
				{
					$this->data[$nameSpace] = (string)$e;
				}
			}
		}
		
	}
	
}