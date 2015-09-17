<?php
namespace app\controller;
use spp\model\CDbMapper;
use app\model\UserDao;
class CIndexController extends CBaseController
{
	public function __construct() {
				
	}
	
	public function indexAction()
	{
		
		$user = CDbMapper::getInstance("user")->findByPk(2,['id','name','email']);
		
		$users = UserDao::newInstance()->getUserList();
		
		var_dump($users);
		
		$this->data['user']= $user;
		$this->render('index/index.html');
		
	}
	
	public function getUserListAction()
	{
		$users = CDbMapper::getInstance("user")->find();
		var_dump($users);
		
		$users = CDbMapper::getInstance("user")->where('age','>',20)->where('sex','=',1)->orderBy('age','desc')->orderBy('name','asc')->limit(1,1)->find(['name']);
		var_dump($users);
		
		$users = CDbMapper::getInstance("user")->whereIn('id',[1,2,3])->orderBy('age','desc')->orderBy('name','asc')->limit(10)->find(['*']);
		
		var_dump($users);
		
		echo CDbMapper::getInstance("user")->whereNotIn('id',[1,2])->orderBy('age','desc')->orderBy('name','asc')->count();
		
		var_dump(CDbMapper::getInstance("user")->distinct('name'));
	}
	
	public function insertUserAction()
	{
		$user = new \stdClass();
		$user->id = 13;
		$user->name = 'starjiang\'1222';
		$user->email = '82776315@qq.com';
		$user->sex = 1;
		//echo self::$userDao->insert($user);
		
		CDbMapper::getInstance("user")->updateByPk($user);
		CDbMapper::getInstance("user")->save($user);
		CDbMapper::getInstance("user")->deleteByPk(1);
		CDbMapper::getInstance("user")->where('id','=',1)->delete();
	}

	
	public function exceptAction(){
		throw  new \Exception("Exception:exceptAction cause");
	}	

	public function infoAction()
	{
		phpinfo();
	}
}