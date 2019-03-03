<?php 

if($function == 'list')
{
    $begin = date("Y-m-d H:i:s", strtotime("-3 days"));
    $now = date("Y-m-d H:i:s");
    $page = isset($_GET['page'])? intval($_GET['page']): 1;
    $size = isset($_GET['size'])?   intval($_GET['size']): 20;
    $starttime = isset($_GET['starttime'])?   $_GET['starttime']: $begin;
    $endtime = isset($_GET['endtime'])?   $_GET['endtime']: $now;
    $start = ($page-1)* $size;
    $where = ['1=1'];
    if(!empty($starttime) && !empty($endtime))
    {
        $where[] = "StartTime BETWEEN '{$starttime}' AND '{$endtime}'";
    }

    if(isset($_GET['name']) && !empty($_GET['name'])){
        $name = addslashes($_GET['name']);
        $where[] = "name like '{$name}%' ";
    }

    if(isset($_GET['commandstring'])&&!empty($_GET['commandstring'])){
        $commandString = addslashes($_GET['commandstring']);
        $where[] ="commandstring like '{$commandString}%' ";
    }

    $statusList = ['RUNNING','FAILED','SUCC','LOST'];
    if(isset($_GET['status'])&&!empty($_GET['status'])){
        $status = strtoupper($_GET['status']);
        if(in_array($status,$statusList)){
            $where[] ="ProcessStatus='{$status}' ";
        }else{
            $back['type'] = 2;
            $back['content'] = 'no such status！';
            $oResponse->ajaxReturn($back, 'error');
        }
    }
    if(isset($_GET['username']) && !empty($_GET['username'])) {
        $userName = $_GET['username'];
        $where[] ="UserName='{$userName}'";
    }
    $sql = "SELECT * FROM `bo_crontab_log` WHERE ". implode(" AND ", $where)." order by `StartTime` DESC  limit {$start}, {$size}";
    //var_dump($sql);die;
    $list = $oMysql->get_all($sql);

    $sql = "SELECT 1 FROM `bo_crontab_log` WHERE ". implode(" AND ", $where);
    $result = $oMysql->get_all($sql);
    $count = count($result);
    $data['list'] = $list;
    $data['count'] = $count;
    $oResponse->ajaxReturn($data);
}else{
    die('wrong functions');
}