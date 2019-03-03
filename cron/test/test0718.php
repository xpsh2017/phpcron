<?php 
/**
 * 处理报警程序
 * 1分钟跑一次
 * 独立于phpcron之外，使用crontab调用
 */
//if(isset($_SERVER["REQUEST_METHOD"])) die("please run this script from CLI.");
include_once(dirname(dirname(dirname(__FILE__))) . "/etc/const.php");
define("PAGE_DEBUG",false);
echo "cron alert start: " . date("Y-m-d H:i:s") . "\n";
$oMysql = new BoDb();
$oRedis = new BoRedis();
$debug = PAGE_DEBUG;
$verbose = true;
$sql = "select * from bo_crontab_log limit 100";
$list = $oMysql->get_all($sql);
//var_dump($list);die;
foreach($list as $v){
    $alert = [
        'log_id' => $v['UUID'],
        //'title' => '输出php模块没有检测到期望输出: SUCC',
        'title' => '该进程没有完成，状态为:FAILED',
        'content' => '命令运行失败',
        'time' => time()
    ];
    push_to_alert_list(json_encode($alert));
}
//var_dump($alert);die;


$oBoAlert = new BoAlert($oMysql, $oRedis, $debug, $verbose);
$oBoAlert->run_alert();
echo "cron alert end: " . date("Y-m-d H:i:s") . "\n";
echo "<< Succ >>\n";



function push_to_alert_list($str)
{
    $oRedis = new BoRedis();
    $list_name = 'alert_list';
    $oRedis->lpush($list_name, $str);
}