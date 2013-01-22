<?php
define("SPP_PATH",__DIR__."/../../spp/");
define("APP_PATH",__DIR__."/../../app/");

include SPP_PATH.'base/CSpp.php';
include APP_PATH.'config/config.php';
CSpp::getInstance()->init();
CSpp::getInstance()->run();
