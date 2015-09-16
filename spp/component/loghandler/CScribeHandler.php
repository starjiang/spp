<?php
namespace spp\component\loghandler;

class CScribeHandler implements ILogHandler
{
	const TYPE_MODULE = 1;
	const TYPE_MSG = 2;
	private $handle = null;
	private $host = '127.0.0.1';
	private $port = 5050;

	public function __construct($host='127.0.0.1',$port=5050)
	{
		$this->handle=socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		socket_set_option($this->handle, SOL_SOCKET, SO_REUSEADDR, 1); 
	}

	public function write($msg)
	{
		if(!is_array($msg))
		{
			$msg = pack('nCna*',1,self::TYPE_MSG,strlen($msg),$msg);
		}
		else
		{
			$module = pack('Cna*',self::TYPE_MODULE,strlen($msg['module']),$msg['module']);
			$mmsg = pack('Cna*',self::TYPE_MSG,strlen($msg['msg']),$msg['msg']);
			$msg = pack('na*',2,$module.$mmsg);
		}
			
		socket_sendto($this->handle,$msg,strlen($msg),0,$this->host,$this->port);
	}
		
	public function __destruct()
	{
		socket_close($this->handle);
	}
}