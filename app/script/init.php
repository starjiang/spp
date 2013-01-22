<?php

define("SPP_PATH",__DIR__."/../../spp/");
define("APP_PATH",__DIR__."/../../app/");
include SPP_PATH.'base/CSpp.php';
include __DIR__.'/config.php';
CSpp::getInstance()->init();
