<?php
class CMUser extends CBaseCacheModel
{
	public static $fields = array('id'=>0,'name'=>'','head'=>'');
	public static $cfgInfo = array();
	
	public function __construct()
	{
		if(self::$cfgInfo == null)
		{
			self::$cfgInfo = CCReader::get('cfg.services.mem.'.get_called_class());
		}
		
	}
	
}