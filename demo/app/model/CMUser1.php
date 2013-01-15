<?php
class CMUser1 extends CCacheModel
{
	public static $fields = array('id'=>0,'name'=>'','head'=>'');
	private static $cache = null;
	private static $source = null;
	private static $modifyList = null;
	
	
	protected  function cache()
	{
		if(self::$cache == null)
		{
			$mem = new CMemCache(CConnMgr::init()->mem(get_class($this)));
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
	
}