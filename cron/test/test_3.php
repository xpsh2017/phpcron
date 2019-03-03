<?php
/**
 * 模拟使用mysql
 * cron 表达式: 每5分钟执行一次
 */
echo basename(__FILE__). ' start at :'. date('Y-m-d H:i:s')."\n";
$config = [
    'host' => '127.0.0.1',
    'user' => 'root',
    'pass' => 'chukui',
    'name' => 'test',
    'port' => 3306
];
$link = @mysqli_connect($config['host'], $config['user'], $config['pass']);
if(mysqli_connect_errno())
{
    $errmsg = 'Mysql Connect failed: ' . mysqli_connect_error();
    die($errmsg);
}
$sql = "show full processlist";
$rsid = mysqli_query($link, $sql);
$result = mysqli_fetch_array($rsid, MYSQLI_ASSOC);
var_dump($result);
echo basename(__FILE__). ' end at :'. date('Y-m-d H:i:s')."\n";