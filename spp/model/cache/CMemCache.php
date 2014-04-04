<?php
class CMemCache implements ICache
{
	//update by voice hu
	private $memcached = null;
	//old memcached
	private $oldmemcached = null;
	
	public function __construct($memcacheds = null)
	{
		if(is_array($memcacheds))
		{
			if(count($memcacheds) != 2)
			{
				throw new Exception("config error", "config error");
			}
			else 
			{
				//a memcached
				$this->memcached = $memcacheds[0];
				//a old memcached
				$this->oldmemcached = $memcacheds[1];
			}
		}
		else 
		{
			$this->memcached = $memcacheds;
		}
	}
	
	public function set($key,$val)
	{
		return $this->memcached->set($key,$val,MEMCACHE_COMPRESSED);
	}
	
	//add by voice hu
	public function _get($key)
	{
		if($this->memcached == null)
		{
			return false;
		}
		//从当前memcached读取
		$data = $this->memcached->get($key);
		if($data != false)
		{
			return $data;
		}
		
		//如果从原表没读到数据，则从旧表中读取数据
		if($this->oldmemcached != null)
		{
			$data = $this->oldmemcached->get($key);
			if($data != false)
			{
				//set new memcached
				$this->set($key,$data);
				//delete old memcached
				$this->oldmemcached->delete($key);
				return $data;
			}
		}
		
		return false;
	}
	
	public function get($keys)
	{
		//如果是key数组，且只有一个数据
//		if(is_array($keys) && count($keys) == 1)
//		{
//			$keys = $keys[0];
//		}
//		
		//如果是选择多key
		if(is_array($keys))
		{
			$result = array();
			foreach ($keys as $key => $value)
			{
				$data = $this->_get($value);
				if($data != false)
				{
					$result[$value] = $data;
				}
			}
			return $result;
		}
		//如果是单key
		return $this->_get($keys);
	}
	
	public function delete($key)
	{
		//delete from oldmemcached
		if($this->oldmemcached != null)
		{
			$this->oldmemcached->delete($key);
		}
		return $this->memcached->delete($key);
	}
}