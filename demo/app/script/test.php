<?php

define("SPP_PATH",__DIR__."/../../../");
define("APP_PATH",__DIR__."/../");
include SPP_PATH.'base/CSpp.php';
include APP_PATH.'config/CConfig.php';
CSpp::getInstance()->init();

CCReader::init(0x1111);

echo microtime()."\n";
for($i=0;$i<10000;$i++)
{
	CCReader::get("cfg.tests.test9999");
}
echo microtime()."\n";
