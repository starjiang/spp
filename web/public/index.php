<?php
define("BASE_PATH",__DIR__."/../../");
include BASE_PATH.'spp/base/CSpp.php';
include BASE_PATH.'config/Config.php';

use spp\base\CSpp;

CSpp::getInstance()->init();
CSpp::getInstance()->run();

