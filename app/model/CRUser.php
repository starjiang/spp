<?php
class CRUser extends CRedisModel
{
	public static $fields = array('id'=>0,'name'=>'','head'=>'');
	private static $redis = null;
	private static $cfg = array();
	
	public function __construct()
	{
		if(self::$cfg == null)
		{
			self::$cfg = CCReader::get('cfg.services.redis.'.get_called_class());
		}
	
	}
	protected  function prefix()
	{
		return 'user';
	}
	
	protected function redis()
	{
		if(self::$redis == null)
		{
			self::$redis = CConnMgr::init()->redis(self::$cfg);
		}
		return self::$redis;
	}
	
}