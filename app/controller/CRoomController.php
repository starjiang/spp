<?php
class CRoomController extends CBaseController
{
	
	
	
	
	
	public function getInfosAction()
	{
		$this->data['ret'] = 0;
		$this->data['msg'] = 'ok';
		
		$type = $_REQUEST['type'];
		
		$roomIds = CCReader::get('cfg.rooms.'.$type);
		$rooms = CCReader::mget($roomIds);
		$this->data['infos'] = $rooms;
		echo json_encode($this->data);
	}
	
	public function getInfoAction()
	{
		$this->data['ret'] = 0;
		$this->data['msg'] = 'ok';
		
		$roomId = $_REQUEST['roomId'];
		$type = $_REQUEST['type'];
		
		$room = CCReader::get('cfg.rooms.'.$type.'.'.$roomId);
		$this->data['info'] = $room;
		echo json_encode($this->data);
	}
}