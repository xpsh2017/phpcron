<?php
class CrontabLogModel {
    //public $insert_allow_values = ['Name', 'TimeString', 'CommandString', 'LogFileString', 'TimeOutSecond', 'IsActive', 'AlertType', 'UserName'];
    //public $update_allow_values = ['UUID', 'Name', 'TimeString', 'CommandString', 'LogFileString', 'TimeOutSecond', 'IsActive', 'AlertType', 'UserName'];
    //public $number_values = ['TimeOutSecond'];
    public $not_empty_values = [
        'Name' => '脚本名字',
        'TimeString' => 'Cron 表达式',
        'CommandString' => '执行命令'
    ];
    public $oResponse = null;
    public $oRequest = null;
    public $oMysql = null;
    public function __construct($mysql, $request, $response)
    {
        $this->oMysql = $mysql;
        $this->oRequest = $request;
        $this->oResponse = $response;
    }

    public function getDataByID($uuid)
    {
        $sql = "SELECT * FROM `bo_crontab_log` WHERE `UUID` = '{$uuid}'";
        return $this->oMysql->get_one($sql);
    }


}