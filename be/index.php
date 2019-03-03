<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
if(!isset($_GET['action']))    die('no action');
$action = strtolower($_GET['action']);
$function = isset($_GET['func']) ?  $_GET['func'] : 'list';
$oMysql = new BoDb();
$oRequest = new Request();
$oResponse = new Response();

if($action == 'cron')
{
    include_once './cron.php';
}elseif($action == 'user'){
    include_once './user.php';
}elseif($action == 'cron_log'){
    include_once './cronlog.php';
}elseif($action == 'group'){
    include_once './group.php';
}elseif($action == 'server'){
    include_once './server.php';
}else{
    die('wrong action');
}
