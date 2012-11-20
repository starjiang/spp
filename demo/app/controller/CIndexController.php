<?php
class CIndexController extends CController
{

	public function before()
	{
		CCReader::init(CConfig::$shmMKey,CConfig::$shmSKey);
	}
	
	public function indexAction()
	{	
		//CSpp::getInstance()->getLogger()->debug('request start');
		echo microtime();
		var_dump(CMUser::model()->setKey('1111')->setHead('yyyyyyyyyyyyyy')->setName('starjiang1')->save());
		echo microtime();
		var_dump(CMUser::model()->get('1111'));
		echo microtime();
		var_dump(CMUser1::model()->get('1111'));
		echo microtime();
	}
	
	public function redisAction()
	{
		//CSpp::getInstance()->getLogger()->debug('request start');
		echo microtime();
		var_dump(CRUser::model()->setKey('1111')->setHead('yyyyyyyyyyyyyy')->setName('starjiang1')->save());
		echo microtime();
		var_dump(CRUser::model()->get('1111'));
		echo microtime();
	}
	
	public function aAction()
	{
		//$paser = new CXMLPaser;
		//$paser->init('test.xml');
		//var_dump($paser->toArray());
		//$paser->toShm(0x1111);
		echo microtime()."<br/>";
		CCReader::init(CConfig::$shmKey);
		echo microtime()."<br/>";
		CCReader::get("cfg.items.item1");
		var_dump(CCReader::mget(array("cfg.items.item1","cfg.items.item2","cfg.items.item41")));
		echo microtime()."<br/>";
		$user = CMUser::model()->get('starjiang');
		echo microtime()."<br/>";
		/*
		var_dump(CCReader::get("cfg.items"));
		var_dump(CCReader::get("cfg.sys.host"));
		var_dump(CCReader::get("cfg.sys.port"));
		var_dump(CCReader::get("cfg.sys.info"));
		var_dump(CCReader::get("cfg.items.item1"));
		var_dump(CCReader::get("cfg.events.Event1"));
		*/
	}
	
	public function mongoAction()
	{
		$this->data['title'] = 'HelloWorld';
		$this->data['name'] = 'starjiang';
		$this->data['birth'] = '1984/02/10';
	
		$user=CPlayer::model();
		$user->_id='starjiang1';
		$user->nickName='xxxxx1';
		$user->headPic='http://xxx1';
		$user->save();
	
		$user=CPlayer::model();
		$user->_id='starjiang2';
		$user->nickName='xxxxx2';
		$user->headPic='http://xxx2';
		$user->set_id('starjiang2')->setNickName('ssss');
	
		$user->save();
	
		$user=CPlayer::model();
		$user->_id='starjiang3';
		$user->nickName='xxxxx3';
		$user->headPic='http://xxx3';
	
		$user->save();
	
		//$user=CPlayer::model();
		//$user->userId='starjiang5';
		//$user->nickName='xxxxx5';
		//$user->headPic='http://xxx5';
	
		//$user->save();
	
	
		$user2=CPlayer::model()->get('starjiang1');
	
		var_dump($user2);
	
		$users=CPlayer::mget(array('starjiang1','starjiang2','starjiang3','starjiang5'));
	
		var_dump($users);
		
		$users=CPlayer::query(array('nickName'=>'xxxxx2'));
		
		echo json_encode($users);
	
		//CPlayer::model()->delete('starjiang5');
		$this->render('index/index.tpl');
	
	}
	
	public function dbAction()
	{
		var_dump(CDBUser::model()->setKey('1001')->setHead('adadads')->save());
		var_dump(CDBUser::model()->get('1001'));
	}
	
	public function sfdbAction()
	{
		var_dump(CSFDBUser::model()->setKey('1002ff')->setHead('ad')->save());
		var_dump(CSFDBUser::model()->get('1002'));
		
		var_dump(CSFDBUser::mget(array(1001,1002)));
	}
	
	public function lbdbAction()
	{
		var_dump(CLBDBUser::model()->setKey('1004')->setHead('ad')->save());
		var_dump(CLBDBUser::model()->get('1004'));
	
		var_dump(CLBDBUser::mget(array(1001,1002,1003,1004,1005)));
	}
		
	public function lbmongoAction()
	{
		var_dump(CLBMGUser::model()->setKey('1005')->setHead('ad')->save());
		//var_dump(CLBMGUser::model()->get('1004'));
	
		var_dump(CLBMGUser::mget(array(1001,1002,1003,'1004','1005')));
	}
	
	public function trAction()
	{
		//var_dump(CTRUser::model()->setKey('1004')->setHead('ad')->save());
		//var_dump(CTRUser::model()->get('1007'));
	
		var_dump(CTRUser::mget(array(1001,1002,1003,'1004','1005',1007)));
	}
}