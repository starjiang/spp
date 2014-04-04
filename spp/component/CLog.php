<?php
class CLog
{
	private $handler = null;
	
	private $level = 15;
	
	private $uid = null;
	
	public function __construct($handler = null,$level = 15,$uid = null)
	{
		$this->handler = $handler;
		$this->level = $level;
		$this->uid = $uid;
	}
	
	public function setUid($uid)
	{
		$this->uid = $uid;
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
	
	public function action($msg,$uid = null)
	{
		$time = date('Y-m-d H:m:s');
		$str = time().'|'.$time;
		if($uid)
			$str .= '|'. $uid;
		$str .= '|'.$msg."\n";
		$this->handler->write($str);
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
	
	public function formatArray($array)
	{
		$result = '';
		$index = 0;
		foreach ($array as $k => $v)
		{
			if(!$index)
				$result .= $k.'=>'.$v;
			else 
				$result .= ','.$k.'=>'.$v;
			$index++;
		}
		return $result;
	}
	
	protected function addBacktrace(&$message){
		$traceInfo = '';
		foreach(array_reverse(debug_backtrace()) as $debug){
			if(isset($debug['file']) && isset($debug['line']) && strstr($debug['file'], "CLog") === false){
				if(strlen($traceInfo) > 0){
					$traceInfo .= '==>';
				}
				$traceInfo .= $debug['file'] . ':' . $debug['line'];
			}
		}
		$traceInfo = ' [' . $traceInfo . ']';
		$message .= $traceInfo;
		if($this->uid)
			$message .= '['. $this->uid . ']';
		if($_REQUEST)
		{
			$message .= '[request:{'.$this->formatArray($_REQUEST).'}]';
		}
		if($_COOKIE)
		{
			$message .= '[cookie:{'.$this->formatArray($_COOKIE).'}]';
		}
	}
	
	protected function write($level,$msg)
	{
		if(($level & $this->level) == $level )
		{
			$this->addBacktrace($msg);
			$msg = '['.date('Y-m-d H:i:s').']['.$this->getLevelStr($level).'] '.$msg."\n\n";
			$this->handler->write($msg);
		}
	}
}