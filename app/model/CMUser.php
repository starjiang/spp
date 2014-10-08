<?php
class CMUser extends CBaseCacheModel
{
	protected  static $fields = array('id'=>0,'name'=>'','brand'=>'','price'=>0,'style'=>'','category'=>'',
			'color'=>'','detail_images'=>'','thumb_images'=>'','shop_url'=>'',
	);
	
	protected static $cfg = null;
	
	public function __construct()
	{
		if(self::$cfg == null)
		{
			self::$cfg = CCReader::get('cfg.services.mem.'.get_called_class());
		}
	
	}
}