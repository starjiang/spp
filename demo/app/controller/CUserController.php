<?php
class CUserController extends CController
{
	public function regAction()	
	{
		var_dump(CUser::model()->setKey(1001)->setNick('starjiang1')->setPwd('msconfig')->save());
		//var_dump(CUser::model()->get(1001));
		//var_dump(CMUser::model()->setKey('1001')->setName('starjiang11cd1')->setHead('8334334')->save());
		//var_dump(CMUser::mget(array(1,2,3,4,5,6,'1001')));
	}
	
}