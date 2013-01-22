<?php
include 'init.php';

if(count($argv) < 2)
{
	echo "usage:".$argv[0]." xml \n";
	return;
}

$model = $argv[1];
$prefix = $argv[2];
$bucket = $argv[3];

$redis = CConnMgr::init()->redis("CUserList");

if($redis == null)
{
	echo "redis is null"
}