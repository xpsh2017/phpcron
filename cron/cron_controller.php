<?php
if(isset($_SERVER["REQUEST_METHOD"])) die("please run this script from CLI.");
if(!function_exists('pcntl_fork')) die('no pcntl_fork functions');
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
define("PAGE_DEBUG",false);

$longopts  = array("timestamp::");
$options = getopt("", $longopts);

$timestamp = isset($options["timestamp"]) ? $options["timestamp"] : 0;
if(!$timestamp || !is_numeric($timestamp)) $timestamp = time();
echo "cron start: " . date("Y-m-d H:i:s") . "\n";
$oMysql = new BoDb();
$oRedis = new BoRedis();
$debug = PAGE_DEBUG;
$verbose = true;
$oBoCrontab = new BoCrontab($oMysql, $oRedis, $debug, $verbose);
$oBoCrontab->run_crontab($timestamp);
echo "cron end: " . date("Y-m-d H:i:s") . "\n";
echo "<< Succ >>\n";