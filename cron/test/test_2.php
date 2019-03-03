<?php
/**
 * 模拟访问接口获取数据，存入文件，
 * cron 表达式:  30 15 * * *
 */
echo basename(__FILE__). ' start at :'. date('Y-m-d H:i:s')."\n";
define('DATA_ROOT', dirname(dirname(__FILE__)). '/data');
$start_date = date('Y-m-d', strtotime('-3 days'));
$end_date = date('Y-m-d');
$fp = fopen(DATA_ROOT. '/ssen_ga_data_'.$end_date, 'w');
$url = "http://tool.fyvor.com/index.php?m=Home&c=Google&a=fetchData&site=ssen&d1={$start_date}&d2={$end_date}&source=ga";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_FILE, $fp);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERPWD, 'mg_comm_user:mg_comm_user');
curl_exec($ch);
fclose($fp);
echo basename(__FILE__). ' end at :'. date('Y-m-d H:i:s')."\n";
