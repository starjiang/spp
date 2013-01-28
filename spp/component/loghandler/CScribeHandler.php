<?php
class CScribeHandler implements ILogHandler
{
	private $handle = null;
	private $host = '127.0.0.1';
	private $port = 5050;
	public function __construct($host='127.0.0.1',$port=5050)
	{
		$this->handle=socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
	}

	public function write($msg)
	{
		socket_sendto($this->handle,$msg,strlen($msg),0,$this->host,$this->port);
	}
}