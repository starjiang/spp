<?php

include 'init.php';

if(count($argv) < 2)
{
	echo "usage:".$argv[0]." xml \n";
	return;	
}

echo "load [".$argv[1]."] to shm\n";

$filename = $argv[1];
$paser = new CXMLPaser;
if(!$paser->init($filename))
{
	echo $paser->getErrMsg()."\n";
	return;
}

if(!$paser->toShm(CConfig::$shmMKey,CConfig::$shmSKey))
{
	echo $paser->getErrMsg()."\n";
	return;
}

echo "load ok!\n";
