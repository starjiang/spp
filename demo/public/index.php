<?php
define("SPP_PATH",__DIR__."/../../");
define("APP_PATH",__DIR__."/../app/");

include SPP_PATH.'base/CSpp.php';
include APP_PATH.'config/CConfig.php';
CSpp::getInstance()->init();
CSpp::getInstance()->run();