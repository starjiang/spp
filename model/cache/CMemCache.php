<?php
class CMemCache implements ICache
{
	private $memcached = null;
	
	public function __construct($memcached = null)
	{
		$this->memcached = $memcached;
	}
	
	public function set($key,$val)
	{

		return $this->memcached->set($key,$val);
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