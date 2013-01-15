<?php
class CConfig
{
	public static $sppPath = SPP_PATH;
	public static $appPath = APP_PATH;
	
	public static $path = ['../app/controller' , '../app/lib' , '../app/model'];
	public static $error = ['cls' => 'CErrController' , 'method' => 'errHandler'];
	public static $tpl = ['path' => '../app/template'];
	public static $log = ['path'=>'../app/logs/' , 'level' => 15];
	
	public static $shmKey = 0x1111;
	
	
	
}