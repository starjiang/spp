<?php
namespace spp\component;

class CCReader
{
	private static $shmMHashMap = null;
	private static $shmSHashMap = null;
	private static $shmSKey = null;
	private static $shmMKey = null;
	private static $errmsg = '';
	private function __construct(){}

	
	public static function init($shmMKey,$shmSKey)
	{

		if(CCReader::$shmMHashMap === null && CCReader::$shmSHashMap === null )
		{
			CCReader::$shmSKey = $shmSKey;
			CCReader::$shmMKey = $shmMKey;

			CCReader::$shmMHashMap = new CShmHashMap();

			if(!CCReader::$shmMHashMap->attach($shmMKey))
			{
				CCReader::$shmMHashMap = null;

				return CCReader::inits($shmSKey);
			}
			else
			{
				return true;
			}
		}
		return true;
	}
	
	public static function getErrMsg()
	{
		return self::$errmsg;
	}
	
	public static function mget($keys)
	{
		
		if(CCReader::$shmMHashMap != null)
		{
			$data = array();
			foreach($keys as $key)
			{
				$value = CCReader::get($key);
				if($value)
				{
					$k = explode('.', $key);
					$data[array_pop($k)]  = $value;
				}
			}
			return $data;				
		}
		return false;
	}
	
	public static function inits($shmSKey)
	{
		if(CCReader::$shmSHashMap === null)
		{
			CCReader::$shmSHashMap = new CShmHashMap();
			if(!CCReader::$shmSHashMap->attach($shmSKey))
			{
				self::$errmsg = self::$shmSHashMap->getErrMsg();
				CCReader::$shmSHashMap = null;
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
		if(CCReader::$shmMHashMap != null)
		{
			$data = CCReader::$shmMHashMap->get($key);
			if(!$data)
			{
				if(CCReader::inits(CCReader::$shmSKey))
				{
					$data = CCReader::$shmSHashMap->get($key);
				}
				else 
				{
					$data = false;
				}
			}
			return $data;
		}
		else if(CCReader::$shmSHashMap!=null)
		{
			return CCReader::$shmSHashMap->get($key);
		}
			
		return false;		
	}
}