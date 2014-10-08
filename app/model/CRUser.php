<?php
class CRUser extends CRedisModel
{
	protected  static $fields = array('id'=>0,'name'=>'','head'=>'');
	
	private static $redis = null;
	protected static $cfg = null;
	
	public function __construct()
	{
		if(self::$cfg == null)
		{
			self::$cfg = CCReader::get('cfg.services.redis.'.get_called_class());
		}
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