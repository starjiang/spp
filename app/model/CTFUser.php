<?php
class CTFUser extends CTFModel
{
	public static $fields = array('id'=>0,'name'=>'','head'=>'');

	protected function from()
	{
		return 'CDBUser';
	}

	protected function to()
	{
		return 'CLBDBUser';
	}
}