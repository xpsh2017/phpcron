<?php
class CrontabModel {
    public $insert_allow_values = ['Name', 'TimeString', 'CommandString', 'LogFileString', 'TimeOutSecond', 'IsActive', 'AlertType', 'UserName','Server', 'SuccFlag'];
    public $update_allow_values = ['UUID', 'Name', 'TimeString', 'CommandString', 'LogFileString', 'TimeOutSecond', 'IsActive', 'AlertType', 'UserName','Server', 'SuccFlag'];
    public $number_values = ['TimeOutSecond'];
    public $not_empty_values = [
        'Name' => '脚本名字',
        'TimeString' => 'Cron 表达式',
        'CommandString' => '执行命令',
        'UserName' => '执行用户'
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
        $sql = "SELECT * FROM `bo_crontab` WHERE `UUID` = '{$uuid}'";
        return $this->oMysql->get_one($sql);
    }

    public function getDataFromPost($func = 'insert')
    {
        $request_arr = $_POST;
        $allow_values = ($func == 'insert')? $this->insert_allow_values: $this->update_allow_values;
        $number_values = $this->number_values;
        $data = [];
        foreach ($request_arr as $key => $value) {
            if(in_array($key, $allow_values))
            {
                if(in_array($key, $number_values))
                    $data[$key] = $this->oRequest->getNumber($key);
                else
                    $data[$key] = $this->oRequest->getStr($key);
            }
        }
        $this->validateData($data);
        $this->checkFilePath($data['LogFileString']);
        return $data;
    }
    public function validateData($data)
    {
        $not_empty_values = $this->not_empty_values;
        foreach ($not_empty_values as $key => $value) {
            if(isset($data[$key]) && empty($data[$key]))
            {
                $back['type'] = 1;
                $back['content'] = $value.' 不能为空 !';
                $this->oResponse->ajaxReturn($back, 'error');
            }
        }
    }
    public function checkFilePath($file_path)
    {
        if(!empty($file_path))
        {
            $file = trim($file_path);
            if($file[0] != '/' && strpos($file_path, '{LOG_DIR}') === false)
            {
                $back['type'] = 3;
                $back['content'] = 'log文件请指定绝对路径！';
                $this->oResponse->ajaxReturn($back, 'error');
            }
            $dir = dirname($file);
            if(!is_dir($dir))
            {
                $back['type'] = 4;
                $back['content'] = '文件路径不存在！';
                $this->oResponse->ajaxReturn($back, 'error');
            }
        }
    }

    public function add($data)
    {
        $uniqueID = new UniqueID();
        $data['UUID'] = $uniqueID->get_uuid('bct');
        if(empty($data['LogFileString']))
        {
            $data['LogFileString'] = '{LOG_DIR}'.$data['UUID'].'.txt';
        }
        $data['CreatedTime'] = date('Y-m-d H:i:s');
        $data['ModifiedTime'] = time();
        $this->oMysql->insert('bo_crontab', $data);
        return $data['UUID'];
    }
    public function update($uuid, $data)
    {
        $where = "UUID='{$uuid}'";
        return $this->oMysql->update('bo_crontab', $data, $where);
    }
}