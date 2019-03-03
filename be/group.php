<?php
/**
 * Created by PhpStorm.
 * User: pxu
 * Date: 2017/7/12
 * Time: 16:36
 */
$crontabModel = new CrontabModel($oMysql, $oRequest, $oResponse);
$tableChangeLog = new TableChangeLog($oMysql);
if($function == 'list')
{
    $sql = "SELECT * FROM `bo_group` WHERE 1 =1 ";
    $list = $oMysql->get_all($sql);
    $back['type'] = 1;
    $back['list'] = $list;
    $oResponse->ajaxReturn($back);

}elseif ($function == 'insert') {
    $data['GroupName'] = $oRequest->getStr('groupname');
    if(!empty($data['GroupName']))
    {
        $sql = "SELECT * FROM `bo_group` WHERE GroupName='{$data['GroupName']}'";
        $res = $oMysql->get_all($sql);
        if($res){
            $back['type'] = 2;
            $back['content'] = 'GroupName is exist';
            $oResponse->ajaxReturn($back,'error');
        }
        $uniqueID = new UniqueID();
        $data['UUID'] = $uniqueID->get_uuid('bg');
        $data['CreateTime'] = date('Y-m-d H:i:s');
        //var_dump($data);die;
        $res = $oMysql->insert('bo_group', $data);
        $back['type'] = 1;
        $back['content'] = $data;
        $oResponse->ajaxReturn($back);
    }else{
        $back['type'] = 2;
        $back['content'] = 'GroupName can not be empty';
        $oResponse->ajaxReturn($back,'error');
    }
}elseif ($function == 'update') {
    $data['GroupName'] = $oRequest->getStr('groupname');
    $where['UUID'] = $oRequest->getStr('UUID');
    if(!empty($data['GroupName'])&&!empty($where['UUID']))
    {
        $sql = "SELECT * FROM `bo_group` WHERE GroupName='{$data['GroupName']}'";
        $res = $oMysql->get_all($sql);
        if($res){
            $back['type'] = 2;
            $back['content'] = 'GroupName is exist';
            $oResponse->ajaxReturn($back,'error');
        }
        $res = $oMysql->update('bo_group', $data,$where);
        $back['type'] = 2;
        $back['content'] = '更新组成功！';
        $oResponse->ajaxReturn($back);
    }else{
        $back['type'] = 2;
        $back['content'] = 'GroupName can not be empty';
        $oResponse->ajaxReturn($back,'error');
    }
}else{
    die('wrong function!');
}