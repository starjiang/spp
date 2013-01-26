<?php
class CDBUser extends CDBModel
{
	public static $fields = array('id'=>0,'name'=>'','head'=>'');
	private static $pdo = null;
	private static $rpdos = null;
	private static $cfg = null;
	
	public function __construct()
	{
		if(self::$cfg == null)
		{
			self::$cfg = CCReader::get('cfg.services.db.'.get_called_class());
		}
	
	}
	protected  function prefix()
	{
		return self::$cfg['prefix'];
	}
	
	protected function pdo()
	{
		if(self::$pdo == null)
		{
			self::$pdo = CConnMgr::init()->pdo(self::$cfg);
		}
		return self::$pdo;
	}
	
	protected function rpdos()
	{
		if(self::$rpdos == null)
		{
			self::$rpdos = CConnMgr::init()->rpdos(self::$cfg);
		}
		return self::$rpdos;
	}
}