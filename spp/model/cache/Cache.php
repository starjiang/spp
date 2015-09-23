<?php
namespace spp\model\cache;
class Cache
{
	static $cacheSets = array();
	static public function getInstance($prefix,$type = 'memcache')
	{
		$key = $type.'.'.$prefix;
		if($type == 'memcache')
		{
			if(static::$cacheSets[$key] == null)
			{
				static::$cacheSets[$key] = new \spp\model\cache\CMemCache($prefix);
			}
			return static::$cacheSets[$key];
		}
		else if($type == 'redis')
		{
			if(static::$cacheSets[$key] == null)
			{
				static::$cacheSets[$key] = new \spp\model\cache\CRedisCache($prefix);
			}
			return static::$cacheSets[$key];
		}
		else
		{
			throw new Exception("not support cache type");
		}
	}
}

