<?php

class Config
{
	//controller namespace;
	public static $controllers = "app.controller";
	//E_ERROR,E_WARNING,E_NOTICE 
	//prod:E_ALL & ~E_NOTICE & ~E_WARNING    dev:E_ALL & ~E_NOTICE
	public static $error = ['display' => 1,'level' => E_ALL & ~E_NOTICE,'handler' => 'CExController::processAction'];
	
	public static $timezone = 'Asia/Shanghai';
	
	public static $tpl = ['path' => '../template'];
	
	//debug 1,info 2,warn 4,error 8
	public static $log = ['path'=>'../logs/' , 'level' => 15];
	
	public static $db = [
		'dsn'=>'mysql:host=127.0.0.1;port=3306;dbname=test',
		'user'=>'root',
		'pwd'=>'',
		'options'=>[PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8',PDO::ATTR_TIMEOUT=>1000,PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
	];
	
	public static $mongo = [
		'dsn'=>'mongodb://127.0.0.1:27017',
		'db'=>'test',
		'options'=>['connectTimeoutMS'=>1000,'connect'=>true]
	];
	
	public static $cache = [
		'memcache' => [['host'=>'127.0.0.1','port'=>11211,'persist'=>true,'weight'=>100,'timeout'=>1],['host'=>'127.0.0.1','port'=>11211]],
		'redis'=>['host'=>'127.0.0.1','port'=>6379,'timeout'=>1,'persist'=>true]
	];
	
	//default rule: /user/info =>  CUserController::infoAction()
	public static $urls = [
		'/home' =>'index/index', //CIndexController::homeAction()
		'/user/info' =>'index/index'
	];
		
	 //xml config saved into share memory,use php shmop extension
	public static $xmlconfig = [
		'enable'=>false,
		'mkey' => 0x1112,//the master shared memory key
		'skey' => 0x2223,//the slave shared memory key
	];
	
}