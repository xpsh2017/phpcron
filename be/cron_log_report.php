<?php

if($function == 'list')
{
    $begin = date("Y-m-d H:i:s", strtotime("-7 days"));
    $now = date("Y-m-d H:i:s");
    $page = isset($_GET['page'])? intval($_GET['page']): 1;
    $size = isset($_GET['size'])?   intval($_GET['size']): 20;
    $starttime = isset($_GET['starttime'])?   $_GET['starttime']: $begin;

    $endtime = isset($_GET['endtime'])?   $_GET['endtime']: $now;
    $start = ($page-1)* $size;
    $where = '';
    if(!empty($starttime) && !empty($endtime))
    {
        $where .= "AND StartTime BETWEEN '{$starttime}' AND '{$endtime}'";
    }

    if(isset($_GET['name'])&&!empty($_GET['name'])){
        $name = addslashes($_GET['name']);
        $where .=" and name like '%{$name}%' ";
    }

    if(isset($_GET['commandstring'])&&!empty($_GET['commandstring'])){
        $commandString = addslashes($_GET['commandstring']);
        $where .=" and commandstring like '{$commandString}%' ";
    }

    $statusList = ['RUNNING','FAILED','SUCC','LOST'];
    if(isset($_GET['status'])&&!empty($_GET['status'])){
        $status = strtoupper($_GET['status']);
        if(in_array($status,$statusList)){
            $where .=" and status='{$status}' ";
        }else{
            echo 'error status';die;
        }
    }
    if(isset($_GET['username'])&&!empty($_GET['username'])) {
        $user = addslashes($_GET['username']);
        $sql = "select groupname from `bo_user` where name='{$user}'";
        $name = $oMysql->get_one($sql);
        if ($name) {
            $where .= " and username='{$name['groupname']}' ";
        } else {
            echo 'no such user';die;
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

    echo json_encode($data);
}else{
    die('wrong functions');
}