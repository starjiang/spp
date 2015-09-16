<?php
namespace app\controller;
use spp\base\CController;
use spp\component\CCReader;

class CBaseController extends CController
{
	public  function  before()
	{
		if(!CCReader::init(\Config::$mshmkey, \Config::$sshmkey))
		{
			throw new \Exception('config init fail,'.CCReader::getErrMsg());
		}
		
		return true;
	}
	
	public function after()
	{
		
	}
}