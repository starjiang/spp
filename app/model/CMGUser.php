<?php
class CMGUser extends CMongoModel
{
	public static $fields = array('uid'=>'','nick'=>'','head'=>'','score'=>0,'coins'=>0,'xp'=>0,'pwd'=>'');
	private static $mongodb = null;
	private static $rmongodbs = null;
	private static $cfg = null;
	
	public function __construct()
	{
		if(self::$cfg == null)
		{
			self::$cfg = CCReader::get('cfg.services.mongo.'.get_called_class());
		}
	
	}
	
	protected  function mongodb()
	{
		if(self::$mongodb == null)
		{
			self::$mongodb = CConnMgr::init()->mongo(self::$cfg);
		}
		return self::$mongodb;
	}

	protected  function rmongodbs()
	{
		if(self::$rmongodbs == null)
		{
			self::$rmongodbs = CConnMgr::init()->rmongos(self::$cfg);
		}
		return self::$rmongodbs;
	}
	
	protected function prefix()
	{
		return 'users';
	}
}