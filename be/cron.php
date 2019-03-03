<?php
$crontabModel = new CrontabModel($oMysql, $oRequest, $oResponse);
$tableChangeLog = new TableChangeLog($oMysql);
$tool = new Tool;
if($function == 'list')
{
    $page = isset($_GET['page'])? intval($_GET['page']): 1;
    $size = isset($_GET['size'])?   intval($_GET['size']): 20;
    $start = ($page-1)* $size;

    $where = ['1=1'];
    $name = $oRequest->getStr('name');
    $commandString = $oRequest->getStr('commandstring');
    $isActive = $oRequest->getStr('isactive');
    $alertType = $oRequest->getStr('alerttype');
    $executeUser = $oRequest->getStr('username');
    if(!empty($name))    $where[] = "`Name` like '{$name}%'";
    if(!empty($commandString))    $where[] = "`CommandString` like '{$commandString}%'";
    if(!empty($isActive)){
        $status_arr = ['YES', 'NO'];
        $tool->filter_query($isActive, $status_arr, $oResponse);
        $where[] = "`IsActive` = '{$isActive}'";
    }
    if(!empty($alertType)){
        $alert_arr = ['EMAIL', 'NOALERT', 'SHELLCMD'];
        $tool->filter_query($alertType, $alert_arr, $oResponse);
        $where[] = "AlertType = '{$alertType}'";
    }
    if(!empty($executeUser)) {
        $where[] = "UserName = '{$executeUser}'";
    }

    $sql = "SELECT * FROM `bo_crontab` WHERE ". implode(" AND ", $where)." limit {$start}, {$size}";
    $list = $oMysql->get_all($sql);
    $sql = "SELECT 1 FROM `bo_crontab` WHERE ". implode(" AND ", $where);
    $result = $oMysql->get_all($sql);
    $count = count($result);
    $data['list'] = $list;
    $data['count'] = $count;
    $oResponse->ajaxReturn($data);
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