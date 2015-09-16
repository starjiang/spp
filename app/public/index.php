<?php
define("SPP_PATH",__DIR__."/../../");
define("APP_PATH",__DIR__."/../../");

use spp\base\CSpp;

include SPP_PATH.'spp/base/CSpp.php';
include APP_PATH.'app/config/Config.php';

CSpp::getInstance()->init();
CSpp::getInstance()->run();

