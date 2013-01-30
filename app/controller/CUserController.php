<?php
class CUserController extends CBaseController
{
	public function regAction()	
	{
		
		$this->data['ret'] = 0;
		$this->data['msg'] = 'ok';
		$name = $_REQUEST['name'];
		$nick = $_REQUEST['nick'];
		$pwd1 = $_REQUEST['pwd1'];
		$pwd2 = $_REQUEST['pwd2'];
		
		if($name == "") 
		{
			$this->data['ret'] = 1;
			$this->data['msg'] = 'name is empty';
			echo json_encode($this->data);
			return;
		}
		
		if($pwd1 != $pwd2)
		{
			$this->data['ret'] = 2;
			$this->data['msg'] = 'two pwd not equal';
			echo json_encode($this->data);
			return;
		}
		
		if(CMGUser::model()->get($name) !== false)
		{
			$this->data['ret'] = 3;
			$this->data['msg'] = 'name have register';
			echo json_encode($this->data);
			return;
		}
		
		CMGUser::model()->setKey($name)->setNick($nick)->setPwd($pwd1)->save();
		echo json_encode($this->data);
	}
	
	
	public function checkLoginAction()
	{
		
		$this->data['ret'] = 0;
		$this->data['msg'] = 'ok';
		$name = $_REQUEST['name'];
		$pwd = $_REQUEST['pwd'];
		
		if($name == "")
		{
			$this->data['ret'] = 1;
			$this->data['msg'] = 'name is empty';
			echo json_encode($this->data);
			return;
		}
		
		$user = CMGUser::model()->get($name);
		
		if($user=== false)
		{
			$this->data['ret'] = 2;
			$this->data['msg'] = 'user not exsit';
			echo json_encode($this->data);
			return;
		}

		if($pwd != $user->getPwd())
		{
			$this->data['ret'] = 3;
			$this->data['msg'] = 'password invalid';
			echo json_encode($this->data);
			return;
		}
		
		echo json_encode($this->data);
	}
	
	public function getInfoAction()
	{
		
		$this->data['ret'] = 0;
		$this->data['msg'] = 'ok';
		$name = $_REQUEST['name'];
		
		if($name == "")
		{
			$this->data['ret'] = 1;
			$this->data['msg'] = 'name is empty';
			echo json_encode($this->data);
			return;
		}
		
		$user = CMGUser::model()->get($name);
		
		if($user=== false)
		{
			$this->data['ret'] = 2;
			$this->data['msg'] = 'user not exsit';
			echo json_encode($this->data);
			return;
		}
		$data = $user->toArray();
		unset($data['pwd']);
		$this->data['info']=$data;
		echo json_encode($this->data);
	}
	
	public function testAction()
	{
		
		$this->data['ret'] = 0;
		$this->data['msg'] = 'ok';
		$name = $_REQUEST['name'];
		
		if($name == "")
		{
			$this->data['ret'] = 1;
			$this->data['msg'] = 'name is empty';
			echo json_encode($this->data);
			return;
		}
		
		$user = CMGUser::model()->get($name);
		
		if($user=== false)
		{
			$this->data['ret'] = 2;
			$this->data['msg'] = 'user not exsit';
			echo json_encode($this->data);
			return;
		}
		
		$user->setNick('starjiang')->setHead('http://192.168.242.129/img/head/head1.jpg');
		$user->setCoins(0)->setScore(0)->setXp(0)->setPwd('123')->save();
	}
	
}