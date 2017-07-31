<?php
namespace spp\component\loghandler;

class CConsoleHandler implements ILogHandler
{
	public function write($msg)
	{
		echo $msg;
	}
}
