<?php
namespace app\model;
use spp\model\CDbMapper;

class UserDao1 extends CDbMapper
{
	protected function __construct() {
		parent::__construct('user', 'id');
	}
	
	public function getUserList()
	{
		return $this->find();
	}
}

