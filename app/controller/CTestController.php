<?php
namespace app\controller;
use spp\model\CMongoMapper;

class CTestController extends CBaseController
{
	public function mongoAction()
	{
		CMongoMapper::getInstance('user')->distinct('name');

		var_dump(CMongoMapper::getInstance("user")->findByPk(1));
		$users = CMongoMapper::getInstance("user")->where('_id','<',100)->where('name','=','starjiang4','or')->orderBy('_id','desc')->limit(10,1)->find(['name']);
		
		foreach ($users as $user)
		{
			var_dump($user);
			echo "<br>";
		}
	}
	
	public function mongoInsertAction()
	{
		$obj = new \stdClass();
		$obj->_id = 12;
		$obj->name = 'starjiang11';
		$obj->email = '82776315@qq.com';
		
		echo CMongoMapper::getInstance('user')->insert($obj);
	}
	
	public function mongoSaveAction()
	{
		$obj = new \stdClass();
		$obj->_id = 12;
		$obj->name = 'starjiang12';
		$obj->email = '82776315@qq.com';
		
		echo CMongoMapper::getInstance('user')->save($obj);
	}
	
	public function mongoUpdateAction()
	{
		$obj = new \stdClass();
		$obj->_id = 12;
		$obj->name = 'starjiang12';
		$obj->email = '82776315@qq.com';
		
		echo CMongoMapper::getInstance('user')->updateByPk($obj);
	}
	
	public function mongoDeleteAction()
	{
		CMongoMapper::getInstance('user')->delete(12);
	}
}