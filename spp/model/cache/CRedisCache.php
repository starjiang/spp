<?php
namespace spp\model\cache;
use spp\model\cache\ICache;
use spp\model\CConnMgr;
class CRedisCache implements ICache
{
	private $redis = null;
	private $prefix = '';
	public function __construct($prefix,$redis = null)
	{
		$this->prefix = $prefix;
		
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
		return $this->redis->setex($this->prefix.'.'.$key,$expire,$val);
	}
	
	public function mget($keys)
	{
		$ids = [];
		foreach($keys as $key)
		{
			$ids[$this->prefix.'.'.$key] = $key;
		}
		
		$values = $this->redis->getMultiple(array_keys($ids));
		$size = 0;
		$map = array();
		foreach ($ids as $key =>$id)
		{
			if($values[$size] != false)
				$map[$id] = $values[$size];
			$size++;
		}
		return $map;
	}
	public function get($key)
	{
		return $this->redis->get($this->prefix.'.'.$key);
	}
	
	public function delete($key)
	{
		return $this->redis->delete($this->prefix.'.'.$key);
	}
}