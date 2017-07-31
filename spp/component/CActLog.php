<?php
namespace spp\component;
use spp\component\loghandler;

class CActLog
{
	private static $logHandler=null;
	
	public static function init($host='127.0.0.1',$port=5050)
	{
		if(self::$logHandler == null)
			self::$logHandler = new loghandler\CScribeHandler($host,$port);
	}
	public static function log($module,$msg)
	{
		if(self::$logHandler !=null)
		{
			$data['module']=$module;
			$data['msg']="[".date('Y-m-d H:i:s')."] ".$msg;
			self::$logHandler->write($data);
		}
	}
}