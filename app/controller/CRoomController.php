<?php
class CRoomController extends CBaseController
{
	public function getInfosAction()
	{
		$roomIds = CCReader::get('cfg.rooms');
		$rooms=CCReader::mget($roomIds);
	}
	
	public function getInfoAction()
	{
		$roomId = $_REQUEST['roomId'];
		$room = CCReader::get('cfg.rooms.'.$roomId);
		var_dump($room);
	}
}