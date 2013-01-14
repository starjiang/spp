<?php
class CCReader
{
	private static $shmHashMap = null;
	private function __construct(){}

	
	public static function init($shmKey,$size=10000000)
	{
		if(CCReader::$shmHashMap === null)
		{
			CCReader::$shmHashMap = new CShmHashMap();
			if(!CCReader::$shmHashMap->init($shmKey,$size))
			{
				CCReader::$shmHashMap = null;
				return false;
			}
			else
			{
				return true;
			}
		}
		return true;
	}
	public static function get($key)
	{
		if(CCReader::$shmHashMap != null)
			return CCReader::$shmHashMap->get($key);
		return false;		
	}
}