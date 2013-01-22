<?php

include 'init.php';

if(count($argv) < 2)
{
	echo "usage:".$argv[0]." xml \n";
	return;	
}

$filename = $argv[1];


$paser = new CXMLPaser;
if(!$paser->init($filename))
{
	echo "load xml fail\n";
	return;
}

if(!$paser->toShm(CConfig::$shmMKey,CConfig::$shmSKey))
{
	echo "load to shm fail\n";
	return;
}

echo "load ok!\n";
