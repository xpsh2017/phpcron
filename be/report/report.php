<?php
include_once(dirname(dirname(dirname(__FILE__))) . "/etc/const.php");
$oMysql = new BoDb();
$oRequest = new Request();
$oResponse = new Response();

$function = isset($_GET['func']) ? $_GET['func'] : 'list';

if($function == 'list')
{

    $page = isset($_GET['page'])? intval($_GET['page']): 1;
    $size = isset($_GET['size'])?   intval($_GET['size']): 20;

    $start = ($page-1)* $size;

    $where = " and ProcessStatus='RUNNING' ";

    $user = $_SERVER['PHP_AUTH_USER'];
    $sql = "select groupname from `bo_user` as bu LEFT join `bo_group_user` as bgu on bu.UUID=bgu.UserUUID left join `bo_group` as bg ON bgu.GroupUUID=bg.UUID where name='{$user}'";
    $name = $oMysql->get_all($sql);
    if ($name) {
        $nameList = '';
        foreach($name as $v){
            if($v['groupname'] =='admin'){
                $nameList = '';break;
            }
            $nameList .="'".$v['groupname']."',";
        }
        if($nameList){
            $nameList = trim($nameList,',');
            $where .= " and username in ({$nameList}) ";
        }

    }
    $sql = "SELECT * FROM `bo_crontab_log` WHERE 1 =1 {$where} order by `StartTime` DESC  limit {$start}, {$size}";
    //var_dump($sql);die;
    $list = $oMysql->get_all($sql);
    $sql = "SELECT 1 FROM `bo_crontab_log` WHERE 1 = 1 {$where}";
    $result = $oMysql->get_all($sql);
    $count = count($result);
    $data['list'] = $list;
    $data['count'] = $count;
    $oResponse->ajaxReturn($data);

}elseif($function == 'detail'){
    $logUUID = $oRequest->getStr('id');
    $logLength = $oRequest->getNumber('line');
    $logLength = !empty($logLength)?$logLength:10;
    if(!empty($logUUID)){
        $sql = "SELECT LogFileString FROM `bo_crontab_log` WHERE 1 =1 and `UUID`='{$logUUID}' ";
        $list = $oMysql->get_one($sql);
        //$list['LogFileString'] = 'log.txt';
        $file = escapeshellarg($list['LogFileString']); // 对命令行参数进行安全转义
        $line = `tail -n $logLength $file`;
        $back['type'] = 1;
        $back['content'] = $line;
        $oResponse->ajaxReturn($back);

    }else{
        $back['type'] = 2;
        $back['content'] = 'logUUID is empty！';
        $oResponse->ajaxReturn($back, 'error');
    }

}else{
    die('wrong functions');
}