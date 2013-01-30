<?php
class CHallController extends CBaseController
{

	public function getRoomsInfoAction()
	{
		$type = $_REQUEST['type'];
		$ids = CCReader::get('cfg.rooms.'.$type);	
		$this->data = array();
		$this->data['ret'] = 0;
		$this->data['msg'] = 'ok';
		
		if(!$ids)
		{
			$this->data['ret']= 1;
			$this->data['msg']='type not found';
			echo json_encode($this->data);
			return;
		}
		
		$infos = CCReader::mget($ids);
		
		$this->data['infos'] = $infos;
		echo json_encode($this->data);

	}
	
	public function getRoomInfoAction()
	{
		$type = $_REQUEST['type'];
		$roomId = $_REQUEST['id'];
		
		$info = CCReader::get('cfg.rooms.'.$type.".".$roomId);
		
		$this->data = array();
		$this->data['ret'] = 0;
		$this->data['msg'] = 'ok';
	
		if(!$info)
		{
			$this->data['ret']= 1;
			$this->data['msg']='room not found';
			echo json_encode($this->data);
			return;
		}

	
		$this->data['info'] = $info;
		echo json_encode($this->data);
	
	}
}