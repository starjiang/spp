<?php
class CConfig
{

	public static $path = array('../controller' , '../lib' , '../model');
	
	//E_ERROR=1,E_WARNING=2,E_PARSE=4,E_NOTICE=8
	public static $error = array('display' => 1,'level' => 4103,'cls' => 'CErrController' , 'method' => 'errHandler');
	
	public static $tpl = array('path' => '../template');
	public static $log = array('path'=>'./logs/' , 'level' => 15);
	
	public static $shmMKey = 0x1111;
	public static $shmSKey = 0x2222;
	
	
}
