<?php
namespace spp\component\loghandler;

interface ILogHandler
{
	public function write($msg);
	
}