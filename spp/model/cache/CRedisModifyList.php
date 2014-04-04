<?php
class CRedisModifyList implements IModifyList
{
	private $redis = null;
	private $prefix = '';
	private $bucketNum = 3;
	
	public function __construct($redis = null,$prefix = '',$bucketNum = 1)
	{
		$this->redis = $redis;
		$this->prefix = $prefix;
		$this->bucketNum = $bucketNum;
		
	}
	
	public function push($key)
	{
		// update buy voice hu
		
		/*$bucket = 0;
		if(is_int($key))
			$bucket = $key % $this->bucketNum;
		else
			$bucket = crc32($key) % $this->bucketNum;
		
		$this->redis->sAdd($this->prefix."_".$bucket,$key);*/
		try
		{
			$this->redis->sAdd($this->prefix, $key);
		}
		catch (Exception $e)
		{
			return false;
		}
		return true;
	}
	
	public function getAndDel()
	{
		$keys = array();
		try
		{
			$ret = $this->redis->multi()->sMembers($this->prefix)->delete($this->prefix)->exec();
			if(!isset($ret) || !is_array($ret))
			{
            	throw new Exception('redis sMembers,delete flag return wrong');
			}
			$keys = $ret[0];
		}
		catch(Exception $e)
		{
			throw new Exception('redis sMembers,delete flag fail, errMsg='.$e->getMessage());
		}
		return $keys;
	}
}