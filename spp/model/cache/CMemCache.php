<?php
namespace spp\model\cache;
use spp\model\cache\ICache;
use spp\model\CConnMgr;
class CMemCache implements ICache
{
	private $memcached = null;
	
	public function __construct($memcached = null)
	{
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
		return $this->memcached->set($key,$val,MEMCACHE_COMPRESSED,$expire);
	}
	
	public function mget($keys)
	{
		return $this->memcached->get($keys);
	}
	public function get($key)
	{
		return $this->memcached->get($key);
	}
	
	public function delete($key)
	{
		return $this->memcached->delete($key);
	}
}