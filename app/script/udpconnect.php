<?php
include 'init.php';

$scribelog = new CScribeHandler();

$scribelog->write('123456');

CActLog::init();
CActLog::log('xxxxx', '333333333333');