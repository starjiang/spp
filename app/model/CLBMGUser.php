<?php
class CLBMGUser extends CLBMongoModel
{
	public static $fields = array('_id'=>0,'nick'=>'','head'=>'','score'=>0,'coins'=>0,'xp'=>0,'pwd'=>'');

	private static $mongodbs = null;
	
	protected  function mongodbs()
	{
		if(self::$mongodbs == null)
		{
			self::$mongodbs = CConnMgr::init()->mongos(get_class($this));
		}

		return self::$mongodbs;
	}
	
	protected function prefix()
	{
		return 'users';
	}
}