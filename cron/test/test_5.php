<?php
include_once(dirname(dirname(dirname(__FILE__))) . "/etc/const.php");
define("PAGE_DEBUG",false);
$oMysql = new BoDb();
$oRedis = new BoRedis();
$debug = PAGE_DEBUG;
$verbose = true;
$oBoAlert = new BoAlert($oMysql, $oRedis, $debug, $verbose);
$oBoAlert->run_alert();