<?php
class Model {
    public $insert_allow_values = [];
    public $update_allow_values = [];
    public $number_values = [];
    public $not_empty_values = [];
    public $oResponse = null;
    public $oRequest = null;
    public $oMysql = null;

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
        return $data;
    }
    public function validateData($data)
    {
        $not_empty_values = $this->not_empty_values;
        foreach ($not_empty_values as $key => $value) {
            if(isset($data[$key]) && empty($data[$key]))
            {
                $back['type'] = 1;
                $back['content'] = $value.' can not be empty !';
                $this->oResponse->ajaxReturn($back, 'error');
            }
        }
    }
}