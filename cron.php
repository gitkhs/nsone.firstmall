<?php
error_reporting(0);	// E_ALL & ~E_NOTICE
define('_IS_SHELL_MODE_', 'Y');
$_THIS_FILE_PATH_		= dirname(__FILE__);
require_once($_THIS_FILE_PATH_	. '/index.php');
require_once(APPPATH ."controllers/_gabia".EXT);

$cronObj	= new _gabia();
$cronObj->daily_cron_method();
?>