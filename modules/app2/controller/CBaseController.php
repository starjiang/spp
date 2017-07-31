<?php
namespace app2\controller;
use spp\base\CController;
use spp\component\CCReader;

class CBaseController extends CController
{
	public  function  before()
	{
		if(isset(\Config::$xmlconfig) && \Config::$xmlconfig['enable'])
		{
			if(!CCReader::init(\Config::$xmlconfig['mkey'], \Config::$xmlconfig['skey']))
			{
				throw new \Exception('config init fail,'.CCReader::getErrMsg());
			}
		}
		return true;
	}
	
	public function after()
	{
		
	}
}