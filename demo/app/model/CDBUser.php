<?php
class CDBUser extends CDBModel
{
	public static $fields = array('id'=>0,'name'=>'','head'=>'');
	private static $pdo = null;
	private static $rpdos = null;
	protected  function prefix()
	{
		return 'user';
	}
	
	protected function pdo()
	{
		if(self::$pdo == null)
		{
			self::$pdo = CConnMgr::init()->pdo(get_class($this));
		}
		return self::$pdo;
	}
	
	protected function rpdos()
	{
		if(self::$rpdos == null)
		{
			self::$rpdos = CConnMgr::init()->rpdos(get_class($this));
		}
		return self::$rpdos;
	}
}