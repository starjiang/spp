<?php
class CLBRUser extends CLBRedisModel
{
	public static $fields = array('id'=>0,'name'=>'','head'=>'');
	private static $rediss = null;
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
	
	protected function rediss()
	{
		if(self::$rediss == null)
		{
			self::$rediss = CConnMgr::init()->rediss(self::$cfg);
		}
		return self::$rediss;
	}
	
}