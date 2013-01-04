<?php
class CMUser extends CCacheModel
{
	private $fields = array('id','name','head');
	private static $cache = null;
	private static $source = null;
	private static $modifyList = null;
	
	
	protected  function cache()
	{
		if(self::$cache == null)
		{
			$memcache = new Memcache();
			$memcache->addServer('127.0.0.1',11211,true);
			$mem = new CMemCache($memcache);
			self::$cache = $mem;
		}
		return self::$cache;
	}
	
	protected function source()
	{
		if(self::$source == null)
		{
			self::$source = new CMysqlSource('CDBUser');
		}
		
		return self::$source;
	}
	
	protected function persist()
	{
		return true;
	}
	
	protected  function prefix()
	{
		return 'user';
	}
	
	protected function fields()
	{
		return $this->fields;
	}
	
}