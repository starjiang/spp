<?php
class CUtils
{
	public static function encode($var)
	{
		return json_encode($var,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
	}		
	
	public static function decode($str)
	{
		return json_decode($str,true);
	}
}