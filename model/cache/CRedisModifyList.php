<?php
class CRedisModifyList implements IModifyList
{
	private $redis = null;
	private $prefix = '';
	private $bucketNum = 3;
	
	public function __construct($redis = null,$prefix = '',$bucketNum = 3)
	{
		$this->redis = $redis;
		$this->prefix = $prefix;
		$this->bucketNum = $bucketNum;
	}
	
	public function push($value)
	{
		$bucket = crc32($value) % $this->bucketNum;
		$this->redis->sAdd($this->prefix."_".$bucket,$value);
	}

}