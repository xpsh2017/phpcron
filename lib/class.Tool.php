<?php 
class Tool {
    public function check_email($email_str)
    {
        if(empty($email_str) || strpos($email_str, '@') === FALSE) return FALSE;  
        list($e, $d) = explode('@', $email_str);  
        if(!empty($e) && !empty($d))  
        {  
            $d = strtolower($d);  
            return preg_match('/^[a-z0-9]+([\+_\-\.]?[a-z0-9]+)*/i', $e);  
        }  
        return FALSE;    
    }

    public function filter_query($needle, $statck, $oResponse)
    {
        $needle = strtoupper($needle);
        if(in_array($needle,$statck)){
            return true;
        }else{
            $back['type'] = 2;
            $back['content'] = 'Please choose yes or no';
            $oResponse->ajaxReturn($back, 'error');
        }
    }
}