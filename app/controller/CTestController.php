<?php
namespace app\controller;
use spp\model\CMongoMapper;
use spp\model\CDbMapper;
use app\model\UserDao;
use spp\model\cache\Cache;
use spp\base\CSpp;
use spp\component\CCReader;
use spp\model\CRedisMapper;

class CTestController extends CBaseController
{
	public function indexAction()
	{
		echo microtime()."<br/>";
		$keys = CCReader::get('cfg.services.users');
		var_dump(CCReader::mget($keys));
		$keys = CCReader::get('cfg.services.users');
		var_dump(CCReader::mget($keys));
		$keys = CCReader::get('cfg.services.users');
		var_dump(CCReader::mget($keys));
		$keys = CCReader::get('cfg.services.users');
		var_dump(CCReader::mget($keys));
		echo microtime()."<br>";
		CSpp::getInstance()->getLogger()->debug('indexAction called');
		$user = CDbMapper::getInstance("user")->findByPk(2,['id','name','email']);
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
	
	public function mongoUpdateAction($a1)
	{
		echo $a1;
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
	
	public function cacheAction()
	{
		Cache::getInstance()->set('name','helloworld');
		echo Cache::getInstance()->get('name');
		var_dump(Cache::getInstance()->mget(['name','name1']));
		
		Cache::getInstance('redis')->set('name','starjiang');
		echo Cache::getInstance('redis')->get('name');
		var_dump(Cache::getInstance('redis')->mget(['name','name1']));
	}
	
	public function redisAction()
	{
		$user = new \stdClass();
		$user->name = 'starjiang';
		$user->email = '82776315@qq.com';
		$user->id = 12;
		CRedisMapper::getInstance('user')->set('12',$user);
		var_dump(CRedisMapper::getInstance('user')->get('12'));
		echo "<br>";
		var_dump(CRedisMapper::getInstance('user')->mget(['11','12','13']));
	}
}