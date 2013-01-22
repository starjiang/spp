<?php
class CMGUser extends CMongoModel
{
	public static $fields = array('_id'=>'','nick'=>'','head'=>'','score'=>0,'coins'=>0,'xp'=>0,'pwd'=>'');
	private static $mongodb = null;
	private static $rmongodbs = null;
	
	protected  function mongodb()
	{
		if(self::$mongodb == null)
		{
			self::$mongodb = CConnMgr::init()->mongo(get_class($this));
		}
		return self::$mongodb;
	}

	protected  function rmongodbs()
	{
		if(self::$rmongodbs == null)
		{
			self::$rmongodbs = CConnMgr::init()->rmongos(get_class($this));
		}
		return self::$rmongodbs;
	}
	
	protected function prefix()
	{
		return 'users';
	}
}