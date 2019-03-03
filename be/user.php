<?php 
$tool = new Tool;
$userModel = new UserModel($oMysql, $oRequest, $oResponse);
if($function == 'insert')
{
    $data = $userModel->getDataFromPost();
    // Email检测
    if(!$tool->check_email($data['Email']))
    {
        $back['type'] = 2;
        $back['content'] = 'Wrong Format Email !';
        $oResponse->ajaxReturn($back, 'error');
    }    
    $uuid = $userModel->add($data);
    $group_uuids = isset($_POST['Groups'])? $_POST['Groups']: [];
    $userModel->attach_groups($group_uuids, $uuid);
    $back['type'] = 1;
    $back['content'] = 'Add User Success';
    $oResponse->ajaxReturn($back);
}elseif($function == 'update'){
    $data = $userModel->getDataFromPost('update');
    // Email检测
    if(!$tool->check_email($data['Email']))
    {
        $back['type'] = 2;
        $back['content'] = 'Wrong Format Email !';
        $oResponse->ajaxReturn($back, 'error');
    }
    $uuid = $data['UUID'];
    unset($data['UUID']);
    $flag = $userModel->update($uuid, $data);
    $group_uuids = isset($_POST['Groups'])? $_POST['Groups']: [];
    $userModel->attach_groups($group_uuids, $uuid);
    $back['type'] = 1;
    $back['content'] = 'Update User Success';
    $oResponse->ajaxReturn($back);
}elseif($function == 'list'){
    $page = isset($_GET['page'])? intval($_GET['page']): 1;
    $size = isset($_GET['size'])?   intval($_GET['size']): 20;
    $start = ($page-1)* $size;

    $crontabUUID = $oRequest->getStr('cron_id');
    $cronStr = '';
    if(!empty($crontabUUID))
    {
        $cronStr = " AND bcu.CrontabUUID = '{$crontabUUID}'";
        $sql = "SELECT bu.UUID,bu.Name FROM `bo_user` as bu LEFT JOIN `bo_crontab_user` as bcu on bu.UUID = bcu.UserUUID WHERE 1=1 {$cronStr}";
    }else{
        $sql = "SELECT * FROM `bo_user` as bu WHERE 1=1 limit {$start},{$size}";
    }
    $data = $oMysql->get_all($sql);
    $sql = "SELECT 1 FROM `bo_user` WHERE 1=1";
    $result = $oMysql->get_all($sql);
    $count = count($result);
    $back['count'] = $count;
    $back['type'] = 1;
    $back['list'] = $data;
    $oResponse->ajaxReturn($back);
}elseif($function == 'usergroup'){
    $userUUID = $oRequest->getStr('useruuid');
    if(!empty($userUUID))
    {
        $sql = "SELECT * FROM `bo_group_user` WHERE UserUUID = '{$userUUID}'";
        $data = $oMysql->get_all($sql);
        $back['type'] = 1;
        $back['list'] = $data;
        $oResponse->ajaxReturn($back);
    }
}else{
    die('wrong function !');
}