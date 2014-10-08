<?php
class CBaseController extends CController
{
	public  function  before()
	{
		if(!CCReader::init(CConfig::$shmMKey, CConfig::$shmSKey))
		{
			throw new Exception('config init fail,'.CCReader::getErrMsg());
		}	
		return true;
	}
	
	public function after()
	{
		
	}
}