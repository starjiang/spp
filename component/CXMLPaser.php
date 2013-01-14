<?php

class CXMLPaser
{
	private $shmHashMap = null;
	private $data = array();
	private $xml = null;
	
	public function init($file)
	{
		$this->xml = simplexml_load_file($file);

		if($this->xml === false)
		{
			return false;
		}
		return true;
	}
	
	public function toShm($shmKey,$size = 10000000)
	{
		if($this->shmHashMap === null)
		{
			$this->shmHashMap = new CShmHashMap();
		}
		
		if(!$this->shmHashMap->init($shmKey,$size))
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