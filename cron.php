<?php
$crontabModel = new CrontabModel($oMysql, $oRequest, $oResponse);
$tableChangeLog = new TableChangeLog($oMysql);
if($function == 'list')
{
    $page = isset($_GET['page'])? intval($_GET['page']): 1;
    $size = isset($_GET['size'])?   intval($_GET['size']): 20;
    $start = ($page-1)* $size;
    $where = '';
    if(isset($_POST['username'])&&!empty($_POST['username'])){
        $username = addslashes($_POST['username']);
        $where .=" and `username`='{$username}'";
    }
    $active = ['YES','NO'];
    if(isset($_POST['isActive'])){
        $isactive = strtoupper($_POST['isActive']);
        if(in_array($isactive,$active)){
            $where .= " AND `Site`=''";
        }else{
            echo 'request error';
        }
    }
    $sql = "SELECT * FROM `bo_crontab` WHERE 1 =1  limit {$start}, {$size}";
    $list = $oMysql->get_all($sql);
    $sql = "SELECT 1 FROM `bo_crontab` WHERE 1 = 1";
    $result = $oMysql->get_all($sql);
    $count = count($result);
    $data['list'] = $list;
    $data['count'] = $count;
    echo json_encode($data);
}elseif ($function == 'insert') {
    $data = $crontabModel->getDataFromPost();
    // 检测cron表达式是否合法
    if(!PhpCrontab::isCorrectCronTime($data['TimeString']))
    {
        $back['type'] = 2;
        $back['content'] = 'cron 表达式格式错误！';
        $oResponse->ajaxReturn($back, 'error');
    }
    $id = $crontabModel->add($data);

    //保存alert用户
    $alertUsers = isset($_POST['AlertUsers'])? $_POST['AlertUsers']: '';
    if(!empty($alertUsers))
    {
        $crontabUUID = $id;
        $mutiData = [];
        foreach ($alertUsers as $userUUID) {
            $tmp = [];
            $tmp['CrontabUUID'] = $crontabUUID;
            $tmp['UserUUID'] = $userUUID;
            $mutiData[] = $tmp;
        }
        $oMysql->insert_batch('bo_crontab_user', $mutiData);
    }
    $back['type'] = 1;
    $back['content'] = '新增命令成功!';
    $oResponse->ajaxReturn($back);

}elseif ($function == 'update') {
    $data = $crontabModel->getDataFromPost('update');
    // 检测cron表达式是否合法
    if(!PhpCrontab::isCorrectCronTime($data['TimeString']))
    {
        $back['type'] = 2;
        $back['content'] = 'cron 表达式格式错误！';
        $oResponse->ajaxReturn($back, 'error');
    }
    $uuid = $data['UUID'];
    $old_data = $crontabModel->getDataByID($uuid);
    $operator = 'mark';
    $tableChangeLog->do_log('bo_crontab', 'UUID-'.$uuid, $old_data, $data, $operator);
    unset($data['UUID']);
    $flag = $crontabModel->update($uuid, $data);
    //保存alert用户
    $alertUsers = isset($_POST['AlertUsers'])? $_POST['AlertUsers']: '';
    if(!empty($alertUsers))
    {
        $crontabUUID = $uuid;
        $mutiData = [];
        foreach ($alertUsers as $userUUID) {
            $tmp = [];
            $tmp['CrontabUUID'] = $crontabUUID;
            $tmp['UserUUID'] = $userUUID;
            $mutiData[] = $tmp;
        }
        $oMysql->insert_batch('bo_crontab_user', $mutiData);
    }
    $back['type'] = 2;
    $back['content'] = '更新命令成功！';
    $oResponse->ajaxReturn($back);
}else{
    die('wrong function!');
}