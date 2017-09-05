<?php
namespace app\controller;
use spp\model\CMongoMapper;
use spp\model\CDbMapper;
use app\model\UserDao;
use spp\model\cache\Cache;
use spp\base\CSpp;
use spp\component\CCReader;
use spp\model\CRedisMapper;
use spp\model\CRedisZSet;
use spp\model\CRedisHash;
class CTestController extends CBaseController
{
    //test/index
	public function indexAction()
	{

//		$keys = CCReader::get('cfg.services.users');
//		var_dump(CCReader::mget($keys));
//		$keys = CCReader::get('cfg.services.users');
//		var_dump(CCReader::mget($keys));
//		$keys = CCReader::get('cfg.services.users');
//		var_dump(CCReader::mget($keys));
//		$keys = CCReader::get('cfg.services.users');
//		var_dump(CCReader::mget($keys));

		CSpp::getInstance()->getLogger()->debug('indexAction called');
        
		$user = CDbMapper::getInstance("user")->findByPk(2,['id','name','email']);
        
		$data['user']= $user;
		$this->renderHtml('index/index.html', $data);
		
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
		var_dump(CMongoMapper::getInstance('user')->distinct('name'));

		
		var_dump(CMongoMapper::getInstance("user")->findByPk(12));
		
		$users = CMongoMapper::getInstance("user")->where('_id','<',100)->where('_id','>',2)->where('email','=','82776315@qq.com1')->where('name','=','starjiang4','or')->orderBy('_id','desc')->limit(10)->find(['name','email']);
		
		echo  CMongoMapper::getInstance("user")->where('_id','<',100)->where('_id','>',2)->where('email','=','82776315@qq.com1')->where('name','=','starjiang4','or')->count();

		
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
		Cache::getInstance('user')->set('name','helloworld');
		echo Cache::getInstance('user')->get('name');
		var_dump(Cache::getInstance('user')->mget(['name','name1']));
		
		Cache::getInstance('user','redis')->set('name','starjiang');
		echo Cache::getInstance('user','redis')->get('name');
		var_dump(Cache::getInstance('user','redis')->mget(['name','name1']));
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
	
	public function zsetAction()
	{
		CRedisZSet::getInstance('user')->add(10,'user1');
		CRedisZSet::getInstance('user')->add(13,'user2');
		CRedisZSet::getInstance('user')->add(11,'user4');
		echo CRedisZSet::getInstance('user')->size();
		echo CRedisZSet::getInstance('user')->index('user2');
		echo CRedisZSet::getInstance('user')->score('user2');
		var_dump(CRedisZSet::getInstance('user')->getByOffset(0,-1));
		var_dump(CRedisZSet::getInstance('user')->getByScore(11,10));
	}
	
	public function hashAction()
	{
		CRedisHash::getInstance('login.1')->set('id',12);
		CRedisHash::getInstance('login.1')->set('token','1111111111111111');
		echo CRedisHash::getInstance('login.1')->get('token');
		var_dump(CRedisHash::getInstance('login.1')->all());
		CRedisHash::getInstance('login.1')->mset(['id'=>1,'token'=>'222222222222222']);
		var_dump(CRedisHash::getInstance('login.1')->mget(['id','token']));

	}
}