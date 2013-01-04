<?php
class CConfig
{
	public static $sppPath = SPP_PATH;
	public static $appPath = APP_PATH;
	
	public static $path = array('../app/controller' , '../app/lib' , '../app/model');
	public static $error = array('cls' => 'CErrController' , 'method' => 'errHandler');
	public static $tpl = array('path' => '../app/template');
	public static $log = array('path'=>'../app/logs/' , 'level' => 8);
	
	public static $db=array();
	
	
	
}