<?php
class CCReader
{
	private static $shmHashMap = null;
	private function __construct(){}

	
	public static function init($shmKey)
	{
		if(CCReader::$shmHashMap === null)
		{
			CCReader::$shmHashMap = new CShmHashMap();
			if(!CCReader::$shmHashMap->init($shmKey))
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
	
	public static function mget($keys)
	{
		
		if(CCReader::$shmHashMap != null)
		{
			$data = array();
			foreach($keys as $key)
			{
				$value = CCReader::$shmHashMap->get($key);
				if($value)
				{
					$data[$key]  = $value;
				}
			}
			return $data;				
		}
		return false;
	}
	
	public static function get($key)
	{
		if(CCReader::$shmHashMap != null)
			return CCReader::$shmHashMap->get($key);
		return false;		
	}
}