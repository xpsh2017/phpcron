<?php 
/**
 * 监控入口程序运行情况
 * 5分钟跑一次
 */
if(isset($_SERVER["REQUEST_METHOD"])) die("please run this script from CLI.");
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
define("PAGE_DEBUG",false);
echo "cron check start: " . date("Y-m-d H:i:s") . "\n";
$oMysql = new BoDb();
$oRedis = new BoRedis();
$debug = PAGE_DEBUG;
$verbose = true;
$oBoAlert = new BoAlert($oMysql, $oRedis, $debug, $verbose);
$file = dirname(__FILE__).'/data/cron_check.log';
$line = `tail -n 10 $file`;
if(empty($line))
{
    $list_name = 'alert_list';
    $uniqueID = new UniqueID();

    $alertData = [
           'UUID' => $uniqueID->get_uuid('bal'),
           'LogUUID' => '',
           'Title' => 'cron_crontroller 运行异常',
           'Content' => 'cron_crontroller没有运行',
           'Level' => 9,
           'CreatedTime' => date('Y-m-d H:i:s')
        ];
    $oBoAlert->error_reporting_via_email($alertData['Title'], $alertData['Content'], 'text', TO);
    $oMysql->insert('bo_alert_log', $alertData);
}else{
    file_put_contents($file, '');
    echo "cron_crontroller is ok"."\n";
}

echo "cron check end: " . date("Y-m-d H:i:s") . "\n";
echo "<< Succ >>\n";
