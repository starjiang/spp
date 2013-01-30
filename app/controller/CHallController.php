<?php
class CHallController extends CBaseController
{

	public function getRoomsInfoAction()
	{
		$type = $_REQUEST['type'];
		$ids = CCReader::get('cfg.rooms.'.$type);	
		$ret = array();
		$ret['ret'] = 0;
		$ret['msg'] = 'ok';
		
		if(!$ids)
		{
			$ret['ret']= 1;
			$ret['msg']='type not found';
			echo json_encode($ret);
			return;
		}
		
		$infos = CCReader::mget($ids);
		
		$ret['infos'] = $infos;
		echo json_encode($ret);

	}
	
	public function getRoomInfoAction()
	{
		$type = $_REQUEST['type'];
		$roomId = $_REQUEST['id'];
		
		$info = CCReader::get('cfg.rooms.'.$type.".".$roomId);
		
		$ret = array();
		$ret['ret'] = 0;
		$ret['msg'] = 'ok';
	
		if(!$info)
		{
			$ret['ret']= 1;
			$ret['msg']='room not found';
			echo json_encode($ret);
			return;
		}

	
		$ret['info'] = $info;
		echo json_encode($ret);
	
	}
}