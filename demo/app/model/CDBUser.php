<?php
class CDBUser extends CDBModel
{
	private $fields = array('id','name','head');
	private static $pdo = null;
	
	protected  function prefix()
	{
		return 'user';
	}
	
	protected function pdo()
	{
		if(self::$pdo == null)
		{
			$pdo = new PDO('mysql:dbname=test;host=127.0.0.1','fanqu','vanchu2010');
			self::$pdo = $pdo;
		}
		return self::$pdo;
	}
	
		
	protected function fields()
	{
		return $this->fields;
	}
	
}