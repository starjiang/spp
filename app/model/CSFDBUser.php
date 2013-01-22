<?php
class CSFDBUser extends CSFDBModel
{
	public static $fields = array('id'=>0,'name'=>'','head'=>'');
	private static $pdo = null;
	private static $rpdos = null;
	protected  function prefix()
	{
		return 'user2';
	}
	
	protected function pdo()
	{
		if(self::$pdo == null)
		{
			self::$pdo = CConnMgr::init()->pdo(get_class($this));
		}
		return self::$pdo;
	}

}