<?php
class CLog
{
	private $handler = null;
	
	private $level = 15;
	
	
	public function __construct($handler = null,$level = 15)
	{
		$this->handler = $handler;
		$this->level = $level;
	}
	
	public function debug($msg)
	{
		$this->write(1, $msg);
	}
	
	public function warn($msg)
	{
		$this->write(4, $msg);
	}
	
	public function error($msg)
	{
		$this->write(8, $msg);
	}
	
	public function info($msg)
	{
		$this->write(2, $msg);
	}
	
	private function getLevelStr($level)
	{
		switch ($level)
		{
		case 1:
			return 'debug';
		break;
		case 2:
			return 'info';	
		break;
		case 4:
			return 'warn';
		break;
		case 8:
			return 'error';
		break;
		default:
				
		}
	}
	
	protected function write($level,$msg)
	{
		if(($level & $this->level) == $level )
		{
			$msg = '['.date('Y-m-d H:i:s').']['.$this->getLevelStr($level).'] '.$msg."\n";
			$this->handler->write($msg);
		}
	}
}