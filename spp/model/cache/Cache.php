<?php
namespace spp\model\cache;
class Cache
{
	static $cacheSets = array();
	static public function getInstance($type = 'memcache')
	{
		if($type == 'memcache')
		{
			if(static::$cacheSets[$type] == null)
			{
				static::$cacheSets[$type] = new \spp\model\cache\CMemCache();
			}
			return static::$cacheSets[$type];
		}
		else if($type == 'redis')
		{
			if(static::$cacheSets[$type] == null)
			{
				static::$cacheSets[$type] = new \spp\model\cache\CRedisCache();
			}
			return static::$cacheSets[$type];
		}
		else
		{
			throw new Exception("not support cache type");
		}
	}
}

