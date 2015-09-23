<?php
namespace spp\model\cache;
use spp\model\cache\ICache;
use spp\model\CConnMgr;
class CMemCache implements ICache
{
	private $memcached = null;
	private $prefix = '';
	public function __construct($prefix,$memcached = null)
	{
		$this->prefix = $prefix;
		if($memcached == null)
		{
			$this->memcached = CConnMgr::getInstance()->mem(\Config::$cache['memcache']);
		}
		else
		{
			$this->memcached = $memcached;
		}
	}
	
	public function setMemcache($memcache)
	{
		$this->memcached = $memcache;
	}	
	
	public function set($key,$val,$expire = 0)
	{
		return $this->memcached->set($this->prefix.'.'.$key,$val,MEMCACHE_COMPRESSED,$expire);
	}
	
	public function mget($keys)
	{
		$ids = [];
		foreach($keys as $key)
		{
			$ids[$this->prefix.'.'.$key] = $key;
		}
		
		$values =  $this->memcached->get(array_keys($ids));
		$rows = [];
		foreach($values as $key => $value)
		{
			$rows[$ids[$key]] = $value;
		}
		return $rows;
	}
	public function get($key)
	{
		return $this->memcached->get($this->prefix.'.'.$key);
	}
	
	public function delete($key)
	{
		return $this->memcached->delete($this->prefix.'.'.$key);
	}
}