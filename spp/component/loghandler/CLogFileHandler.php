<?php
namespace spp\component\loghandler;

class CLogFileHandler implements ILogHandler
{
	private $file = '';

	public function __construct($file = '')
	{
		$this->file = $file;
	}
	
	public function write($msg)
	{
		file_put_contents($this->file, $msg, FILE_APPEND|LOCK_EX);	
		@chmod($this->file, 0777);
	}
	
	public function __destruct()
	{

	}
}
