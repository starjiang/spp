<?php

define("SPP_PATH",__DIR__."/../../../");
define("APP_PATH",__DIR__."/../");
include SPP_PATH.'base/CSpp.php';
include APP_PATH.'config/CConfig.php';
CSpp::getInstance()->init();

if(count($argv) < 3)
{
	echo "usage:".$argv[0]." xml shmkey\n";
	return;	
}

$filename = $argv[1];
$shmKey = (int)$argv[2];

if($shmKey == 0)
{
	$shmKey = hexdec($argv[2]);
}

if($shmKey == 0)
{
	echo "shmkey is 0\n";
	return;
}

$paser = new CXMLPaser;
if(!$paser->init($filename))
{
	echo "load xml fail\n";
	return;
}

if(!$paser->toShm($shmKey))
{
	echo "load to shm fail\n";
	return;
}

echo "load ok!\n";