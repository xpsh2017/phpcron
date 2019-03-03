<?php 
/**
 * 处理报警程序
 * 1分钟跑一次
 * 独立于phpcron之外，使用crontab调用
 */
if(isset($_SERVER["REQUEST_METHOD"])) die("please run this script from CLI.");
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
define("PAGE_DEBUG",false);
echo "cron alert start: " . date("Y-m-d H:i:s") . "\n";
$oMysql = new BoDb();
$oRedis = new BoRedis();
$debug = PAGE_DEBUG;
$verbose = true;

$oBoAlert = new BoAlert($oMysql, $oRedis, $debug, $verbose);
$oBoAlert->run_alert();
echo "cron alert end: " . date("Y-m-d H:i:s") . "\n";
echo "<< Succ >>\n";
