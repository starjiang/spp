<?php
namespace spp\model;

class CRedisZSet
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

	public function add($score,$member)
	{
		$this->redis->zAdd($this->prefix,$score,$member);
	}
	public function remove($member)
	{
		$this->redis->zDelete($member);
	}
	public function getByOffset($start,$end,$order='')
	{
		if($order == 'desc')
		{
			return $this->redis->zRevRange($this->prefix,$start,$end);
		}
		else
		{
			return $this->redis->zRange($this->prefix,$start,$end);
		}
	}
	public function getByScore($start,$end,$order='desc')
	{
		if($order == 'desc')
		{
			return $this->redis->zRevRangeByScore($this->prefix,$start,$end);
		}
		else
		{
			return $this->redis->zRangeByScore($this->prefix,$start,$end);
		}
	}
	
	public function count($start,$end)
	{
		return $this->redis->zCount($this->prefix,$start,$end);
	}
	
	public function size()
	{
		return $this->redis->zSize();
	}
}

