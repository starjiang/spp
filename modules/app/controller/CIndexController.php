<?php
namespace app\controller;

// app/index/index or index/index,because app is the default app
class CIndexController extends CBaseController
{
	public function indexAction()
	{
		echo 'hello world';
	}
}