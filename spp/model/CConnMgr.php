<?php
namespace spp\model;

class CConnMgr
{
	private $pdos = array();
	private $mongos = array();
	private $mems = array();
	private $rediss = array();
	
	private static $instance = null;
	
	private function __construct(){}
	
	public static function getInstance()
	{
		if(CConnMgr::$instance == null)
		{
			CConnMgr::$instance = new CConnMgr();
		}
		return CConnMgr::$instance;
	}
	
	public function getPdo($dsn,$user,$pwd,$options)
	{
		
		$key = $dsn;
		
		if($this->pdos[$key] == null)
		{
			$this->pdos[$key] = new \PDO($dsn,$user,$pwd,$options);
		}
		
		return $this->pdos[$key];
		
	}
		
	public function pdo(Array $info)
	{
		if($info ==false)
		{
			return null;
		}
		return $this->getPdo($info['dsn'],$info['user'],$info['pwd'],$info['options']);
	}	
		
	public function getMongo($dsn,$options,$db)
	{
		$key = $dsn."/".$db;
		if($this->mongos[$key] == null)
		{
			$mongoClient = new \MongoClient($dsn,$options);
			$this->mongos[$key] = $mongoClient->selectDb($db);;
		}
		return $this->mongos[$key];
	}
	
	public function mongo(Array $info)
	{
		
		if($info ==false)
		{
			return null;
		}
				
		return $this->getMongo($info['dsn'],$info['options'],$info['db']);
	}
		
	public function getMem($hosts,$ports)
	{
		$key = $hosts.$ports;
		
		if($this->mems[$key] == null)
		{
			$memcache = new \Memcache();
			$ahosts = explode(',',$hosts);
			$aports = explode(',',$ports);
		
			$len = count($ahosts);
		
			for($i=0;$i<$len;$i++)
			{
				$memcache->addServer($ahosts[$i],(int)$aports[$i],true);
			}
			$this->mems[$key] = $memcache;
		}
		
		return $this->mems[$key];
	}
	
	public function mem(Array $info)
	{
		if($info ==false)
		{
			return null;
		}
		return $this->getMem($info['hosts'], $info['ports']);
	}

	public function getRedis($host,$port)
	{
		$key = $host.$port;
		
		if($this->rediss[$key] == null)
		{
			$redis = new \Redis();
			$redis->pconnect($host,$port,1);
			$this->rediss[$key] =$redis;
		}
		
		return $this->rediss[$key];
	}
	
	public function redis(Array $info)
	{
		if($info ==false)
		{
			return null;
		}
		
		return $this->getRedis($info['host'], $info['port']);

	}
}
