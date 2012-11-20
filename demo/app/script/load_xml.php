<?php

define("SPP_PATH",__DIR__."/../../../");
define("APP_PATH",__DIR__."/../");
include SPP_PATH.'base/CSpp.php';
include APP_PATH.'config/CConfig.php';
CSpp::getInstance()->init();

if(count($argv) < 4)
{
	echo "usage:".$argv[0]." xml shmMKey shmSKey\n";
	return;	
}

$filename = $argv[1];
$shmMKey = (int)$argv[2];
$shmSKey = (int)$argv[3];

if($shmMKey == 0)
{
	$shmMKey = hexdec($argv[2]);
}

if($shmMKey == 0)
{
	echo "shmMkey is 0\n";
	return;
}


if($shmSKey == 0)
{
	$shmSKey = hexdec($argv[3]);
}

if($shmSKey == 0)
{
	echo "shmSkey is 0\n";
	return;
}

$paser = new CXMLPaser;
if(!$paser->init($filename))
{
	echo "load xml fail\n";
	return;
}

if(!$paser->toShm($shmMKey,$shmSKey))
{
	echo "load to shm fail\n";
	return;
}

echo "load ok!\n";