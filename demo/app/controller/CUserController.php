<?php
class CUserController extends CController
{
	public function regAction()	
	{
		$ret = array();
		$ret['ret'] = 0;
		$ret['msg'] = 'ok';
		$name = $_REQUEST['name'];
		$nick = $_REQUEST['nick'];
		$pwd1 = $_REQUEST['pwd1'];
		$pwd2 = $_REQUEST['pwd2'];
		
		if($name == "") 
		{
			$ret['ret'] = 1;
			$ret['msg'] = 'name is empty';
			echo json_encode($ret);
			return;
		}
		
		if($pwd1 != $pwd2)
		{
			$ret['ret'] = 2;
			$ret['msg'] = 'two pwd not equal';
			echo json_encode($ret);
			return;
		}
		
		if(CUser::model()->get($name) !== false)
		{
			$ret['ret'] = 3;
			$ret['msg'] = 'name have register';
			echo json_encode($ret);
			return;
		}
		
		if(!CUser::model()->setKey($name)->setNick($nick)->setPwd($pwd1)->save())
		{
			$ret['ret'] = 4;
			$ret['msg'] = 'system error';
			echo json_encode($ret);
			return;
		}
		
		echo json_encode($ret);
	}
	
	
	public function checkLoginAction()
	{
		$ret = array();
		$ret['ret'] = 0;
		$ret['msg'] = 'ok';
		$name = $_REQUEST['name'];
		$pwd = $_REQUEST['pwd'];
		
		if($name == "")
		{
			$ret['ret'] = 1;
			$ret['msg'] = 'name is empty';
			echo json_encode($ret);
			return;
		}
		
		$user = CUser::model()->get($name);
		
		if($user=== false)
		{
			$ret['ret'] = 2;
			$ret['msg'] = 'user not exsit';
			echo json_encode($ret);
			return;
		}

		if($pwd !== $user->getPwd())
		{
			$ret['ret'] = 3;
			$ret['msg'] = 'password invalid';
			echo json_encode($ret);
			return;
		}
		
		echo json_encode($ret);
	}
	
	public function getInfoAction()
	{
		$ret = array();
		$ret['ret'] = 0;
		$ret['msg'] = 'ok';
		$name = $_REQUEST['name'];
		
		if($name == "")
		{
			$ret['ret'] = 1;
			$ret['msg'] = 'name is empty';
			echo json_encode($ret);
			return;
		}
		
		$user = CUser::model()->get($name);
		
		if($user=== false)
		{
			$ret['ret'] = 2;
			$ret['msg'] = 'user not exsit';
			echo json_encode($ret);
			return;
		}
		$data = $user->toArray();
		unset($data['pwd']);
		echo json_encode($data);
	}
	
}