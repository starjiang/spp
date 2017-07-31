<?php
namespace spp\component;

class CXMLPaser
{
	private $shmHashMap = null;
	private $data = array();
	private $xml = null;
	private $childnum = 0 ;
	private $primes = array(12281,21841,38833,69061,122777,218357,388211,517619);
	private $errmsg = '';	
	private $size  = 10*1024*1024;
	public function init($file,$size = 10*1024*1024)
	{
		$this->size = $size;
		$fp = fopen($file,'r');
		$stats = fstat($fp);
		if ($stats['size'] > $this->size)
		{
			throw new \Exception("share memory size less than xmlconfig file size");
		}
		
		$this->xml = @simplexml_load_file($file);

		if($this->xml == false)
		{
			$this->errmsg = "load xml ".$file." fail";
			return false;
		}
		
		return true;
	}
	public function getErrMsg()
	{
		return $this->errmsg;		
	}
	private function getBucketsNum()
	{
		$childNum = $this->countChild($this->xml);
		$buckets = 0;
		foreach($this->primes as $prime)
		{
			$buckets = $prime;
			if($prime > $childNum)
			{
				break;
			}
		}
		return $buckets;
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
	
	
	public function toShm($shmMKey,$shmSKey)
	{
		if($this->shmHashMap === null)
		{
			$this->shmHashMap = new CShmHashMap();
		}
		if(!$this->shmHashMap->create($shmSKey,$this->getBucketsNum(),$this->size))
		{
			$this->errmsg = $this->shmHashMap->getErrMsg();
			return false;
		}
		
		$this->decode($this->xml,true);
		
		$this->shmHashMap = new CShmHashMap();
		
		if(!$this->shmHashMap->create($shmMKey,$this->getBucketsNum(),$this->size))
		{
			$this->errmsg = $this->shmHashMap->getErrMsg();
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