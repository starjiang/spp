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
			$this->cache = new CMemCache(CConnMgr::init()->mem($caller::$cfg));
		}
		return $this->cache;
	}
	
	protected function source()
	{
		$caller = get_called_class();
		
		if($caller::$cfg['source']==null)
			return null;

		if($this->source == null)
			$this->source = new CModelSource($caller::$cfg['source']);
		return $this->source;
	}
	
	protected function persist()
	{
		$caller = get_called_class();
		return (int)$caller::$cfg['persist'];
	}
	
	protected  function prefix()
	{
		$caller = get_called_class();
		return $caller::$cfg['prefix'];
	}

	protected function delayWrite()
	{
		$caller = get_called_class();
		return (int)$caller::$cfg['delay_write'];
	}
	
	protected function modifyList()
	{
		if($this->modifyList == null)
		{
			$caller		= get_called_class();
			$host		= $caller::$cfg['modify_host'];
			$port		= $caller::$cfg['modify_port'];
			$prefix		= $caller::$cfg['modify_prefix'];
			$buckets	= $caller::$cfg['modify_buckets'];
			$this->modifyList = new CRedisModifyList(CConnMgr::init()->getRedis($host, $port), $prefix, $buckets);
		}
		return $this->modifyList;
	}
}
