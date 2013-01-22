<?php
class CRUser extends CRedisModel
{
	public static $fields = array('id'=>0,'name'=>'','head'=>'');
	private static $redis = null;
	
	protected  function prefix()
	{
		return 'user';
	}
	
	protected function redis()
	{
		if(self::$redis == null)
		{
			self::$redis = CConnMgr::init()->redis(get_class($this));
		}
		return self::$redis;
	}
	
}