<?php
class CBaseController extends CController
{
	public  function  before()
	{
		CCReader::init(CConfig::$shmMKey, CConfig::$shmSKey);	
	}
	
	public function after()
	{
		
	}
}