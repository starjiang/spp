<?php
class CIndexController extends CController
{
	
	public function indexAction()
	{	
		CSpp::getInstance()->getLogger()->debug('request start');
		
		
		$this->data['title'] = '蒋有星';
		$this->data['name'] = 'starjiang';
		$this->data['birth'] = '1984/02/10';
		
		
		echo json_encode($this->data);

		$user = CUser::model()->setNickName('starjiang11111111')->setHeadPic('yyyyyyyyyyyyyy')->setUserId('starjiang1');
		
		echo $user->getNickName();
		$user->save();
		
		var_dump($user->toArray());
		
		$user=CUser::model();
		$user->userId='starjiang2';
		$user->nickName='xxxxx2';
		$user->headPic='http://xxx2';

		$user->save();
		
		$user=CUser::model();
		$user->userId='starjiang3';
		$user->nickName='xxxxx3';
		$user->headPic='http://xxx3';

		$user->save();
		
		$user=CUser::model();
		$user->userId='starjiang5';
		$user->nickName='xxxxx5';
		$user->headPic='http://xxx5';
		
		$user->save();
		
		
		$user2=CUser::model()->get('starjiang1');
		
		//var_dump($user2);
		
		$users=CUser::mget(array('starjiang1','starjiang2','starjiang3','starjiang4'));
		
		echo json_encode($users);

		//CUser::model()->delete('starjiang4');
		$this->render('index/index.tpl');
		
	}
	
	public function aAction()
	{
		$user=CMUser::model()->get('1');
		print_r($user);
		$user->setKey('2')->setName('xxxxxxxxxxxxxxx');
		print_r($user);
		echo $user2 = CMUser::model()->getId();
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
		$this->data['title'] = 'HelloWorld';
		$this->data['name'] = 'starjiang';
		$this->data['birth'] = '1984/02/10';
	
		$user=CPerson::model();
		$user->setUserId('starjiang1');
		$user->userId='starjiang1';
		$user->nickName='xxxxx1';
		$user->headPic='http://xxx1';
		$user->save();
	
		$user=CPerson::model();
		$user->userId='starjiang2';
		$user->nickName='xxxxx2';
		$user->headPic='http://xxx2';
	
		$user->save();
	
		$user=CPerson::model();
		$user->userId='starjiang3';
		$user->nickName='xxxxx3';
		$user->headPic='http://xxx3';
	
		$user->save();
	
		//$user=CPlayer::model();
		//$user->userId='starjiang5';
		//$user->nickName='xxxxx5';
		//$user->headPic='http://xxx5';
	
		//$user->save();
	
	
		$user2=CPerson::model()->get('starjiang1');
	
		var_dump($user2);
	
		$users=CPerson::mget(array('starjiang1','starjiang2','starjiang3','starjiang5'));
	
		echo json_encode($users);
	
		$users=CPerson::query("nickName = 'xxxxx2'");
	
		echo json_encode($users);
	
		//CPerson::model()->delete('starjiang5');
		$this->render('index/index.tpl');
	
	}
		
}