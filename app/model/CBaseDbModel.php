<?php
class CBaseDbModel extends CDBModel
{
	protected function pdo()
	{
		$caller = get_called_class();
		
		if($caller::$cfg == null)
		{
			$caller::$cfg = CCReader::get('cfg.services.db.'.get_called_class());
		}
		
		return  CConnMgr::init()->pdo($caller::$cfg);
	}
	
	protected function prefix()
	{
		$caller = get_called_class();
		
		if($caller::$cfg == null)
		{
			$caller::$cfg = CCReader::get('cfg.services.db.'.get_called_class());
		}
		
		return $caller::$cfg['prefix'];
	}
	
}