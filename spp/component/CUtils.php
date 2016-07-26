<?php
namespace spp\component;

class CUtils
{
	public static function json_encode($var)
	{
		return json_encode($var,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
	}		
	
	public static function json_decode($str)
	{
		return json_decode($str,true);
	}
	
	
	public static function encode($var)
	{
		return self::json_encode($var);
	}
	
	public static function decode($str)
	{
		return self::json_decode($str);
	}

}