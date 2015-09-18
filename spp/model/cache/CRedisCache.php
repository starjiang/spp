<?php
namespace spp\model\cache;
use spp\model\cache\ICache;
use spp\model\CConnMgr;
class CRedisCache implements ICache
{
	private $redis = null;
	
	public function __construct($redis = null)
	{
		if($redis == null)
		{
			$this->redis = CConnMgr::getInstance()->redis(\Config::$cache['redis']);
		}
		else
		{
			$this->redis = $redis;
		}
	}
	
	public function set($key,$val,$expire = 3600)
	{
		return $this->redis->setex($key,$expire,$val);
	}
	
	public function mget($keys)
	{
		$values = $this->redis->getMultiple($keys);
		$size = 0;
		$map = array();
		foreach ($keys as $key)
		{
			if($values[$size] != false)
				$map[$key] = $values[$size];
			$size++;
		}
		return $map;
	}
	public function get($key)
	{
		return $this->redis->get($key);
	}
	
	public function delete($key)
	{
		return $this->redis->delete($key);
	}
}