<?php
class CUser extends CMongoModel
{
	private $fields = array('_id','nick','head','score','coins','xp','pwd');
	private static $mongodb = null;
	private static $rmongodbs = null;
	
	protected  function mongodb()
	{
		if(self::$mongodb == null)
		{
			$mongo = new Mongo("mongodb://localhost");
			$mongodb = $mongo->selectDB('zjhdb');
			self::$mongodb = $mongodb;
		}
		return self::$mongodb;
	}

	protected  function rmongodbs()
	{
		if(self::$rmongodbs == null)
		{
			$mongo = new Mongo("mongodb://localhost");
			$mongodb = $mongo->selectDB('zjhdb');
			self::$rmongodbs[] = $mongodb;
			self::$rmongodbs[] = $mongodb;
			self::$rmongodbs[] = $mongodb;
		}
		return self::$rmongodbs;
	}
	
	protected function prefix()
	{
		return 'users';
	}
	
	protected function fields()
	{
		return $this->fields;
	}
	
}