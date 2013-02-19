<?php
class CBaseCacheModel extends CCacheModel
{
	private $cache = null;
	private $source = null;
	private $modifyList = null;

	protected  function cache()
	{
		if($this->cache == null)
		{
			$caller = get_called_class();
			$this->cache = new CMemCache(CConnMgr::init()->mem($caller::$cfgInfo));
		}
		return $this->cache;
	}
	
	protected function source()
	{
		$caller = get_called_class();
		
		if($caller::$cfgInfo['source']==null)
			return null;

		if($this->source == null)
			$this->source = new CMysqlSource($caller::$cfgInfo['source']);
		return $this->source;
	}
	
	protected function persist()
	{
		$caller = get_called_class();
		return (int)$caller::$cfgInfo['persist'];
	}
	
	protected  function prefix()
	{
		$caller = get_called_class();
		return $caller::$cfgInfo['prefix'];
	}

	protected function delayWrite()
	{
		$caller = get_called_class();
		return (int)$caller::$cfgInfo['delay_write'];
	}
	
	protected function modifyList()
	{
		if($this->modifyList == null)
		{
			$caller		= get_called_class();
			$host		= $caller::$cfgInfo['modify_host'];
			$port		= $caller::$cfgInfo['modify_port'];
			$prefix		= $caller::$cfgInfo['modify_prefix'];
			$buckets	= $caller::$cfgInfo['modify_buckets'];
			$this->modifyList = new CRedisModifyList(CConnMgr::init()->getRedis($host, $port), $prefix, $buckets);
		}
		return $this->modifyList;
	}
}
