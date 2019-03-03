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
    $sql = "SELECT * FROM `bo_server` WHERE 1 =1 ";
    $list = $oMysql->get_all($sql);
    //var_dump($list);die;
    $back['type'] = 1;
    $back['list'] = $list;
    $oResponse->ajaxReturn($back);

}elseif ($function == 'insert') {
    $data['Server'] = $oRequest->getStr('server');
    if(!empty($data['Server']))
    {
        $sql = "SELECT * FROM `bo_server` WHERE Server='{$data['Server']}'";
        $res = $oMysql->get_all($sql);
        if($res){
            $back['type'] = 2;
            $back['content'] = 'Server is exist';
            $oResponse->ajaxReturn($back,'error');
        }
        $uniqueID = new UniqueID();
        $data['UUID'] = $uniqueID->get_uuid('bs');
        $data['CreateTime'] = date('Y-m-d H:i:s');
        $res = $oMysql->insert('bo_server', $data);
        $back['type'] = 1;
        $back['content'] = $data;
        $oResponse->ajaxReturn($back);
    }else{
        $back['type'] = 2;
        $back['content'] = 'Server can not be empty';
        $oResponse->ajaxReturn($back,'error');
    }
}elseif ($function == 'update') {
    $data['Server'] = $oRequest->getStr('server');
    $where['UUID'] = $oRequest->getStr('UUID');
    if(!empty($data['Server'])&&!empty($where['UUID']))
    {
        $sql = "SELECT * FROM `bo_Server` WHERE Server='{$data['Server']}'";
        $res = $oMysql->get_all($sql);
        if($res){
            $back['type'] = 2;
            $back['content'] = 'Server is exist';
            $oResponse->ajaxReturn($back,'error');
        }
        $res = $oMysql->update('bo_server', $data,$where);
        $back['type'] = 2;
        $back['content'] = '更新站点成功！';
        $oResponse->ajaxReturn($back);
    }else{
        $back['type'] = 2;
        $back['content'] = 'Server can not be empty';
        $oResponse->ajaxReturn($back,'error');
    }
}else{
    die('wrong function!');
}