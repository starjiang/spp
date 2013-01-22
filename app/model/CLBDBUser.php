<?php
class CLBDBUser extends CLBDBModel
{
	public static $fields = array('id'=>0,'name'=>'','head'=>'');
	private static $pdos = null;

	protected  function prefix()
	{
		return 'user2';
	}
	
	protected function pdos()
	{
		if(self::$pdos == null)
		{
			self::$pdos = CConnMgr::init()->pdos(get_class($this));
		}
		return self::$pdos;
	}

}