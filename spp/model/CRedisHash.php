<?php
namespace spp\model;

class CRedisHash
{
	static  private  $instances = [];
	private $prefix = '';
	private $redis = null;

	protected function __construct($prefix) {
		$this->prefix = $prefix;
		if(isset(\Config::$redis)){
			$this->redis = CConnMgr::getInstance()->redis(\Config::$redis);
		}
	}
	
	public static function newInstance()
	{
		$caller = get_called_class();
		return new $caller();
	}
	
	
	public static function getInstance($prefix)
	{
		$key = $prefix;
		if(self::$instances[$key] == null)
		{
			self::$instances[$key] = new self($prefix);
		}
		return self::$instances[$key];
	}
	
	public function setRedis($redis)
	{
		$this->redis = $redis;
	}

	public function  set($id,$obj)
	{
		return $this->redis->hset($this->prefix,$id,$obj);
	}
	
	public function get($id)
	{
		return $this->redis->hget($this->prefix,$id);
	}
	
	public function len()
	{
		return $this->redis->hLen($this->prefix);
	}
	
	public function contain($id)
	{
		return $this->redis->hExsits($this->prefix,$id);
	}
	
	public function keys()
	{
		return $this->redis->hKeys($this->prefix);
	}
	
	public function values()
	{
		return $this->redis->hVals($this->prefix);
	}
	
	public function mget($ids)
	{
		return $this->redis->hMGet($this->prefix,$ids);
	}
	
	public function mset($obj)
	{
		$this->redis->hMSet($this->prefix,$obj);
	}
	
	public function all()
	{
		return $this->redis->hGetAll($this->prefix);
	}

	public function  delete($id)
	{
		$this->redis->hDel($this->prefix,$id);
	}
	
	
}

