<?php
class BoAlert {
    public $oMysql;
    public $oRedis;
    public $debug = false;
    public $verbose = false;
    public $redisKey = 'alert_list';

    public function __construct($oMysql, $oRedis, $debug=null, $verbose=null)
    {
        $this->oMysql = $oMysql;
        $this->oRedis = $oRedis;
        if(isset($debug)) $this->debug = $debug;
        if(isset($verbose)) $this->verbose = $verbose;
    }

    public function run_alert()
    {
        //从redis 队列中读取报警
        $alert_list = $this->get_alert_list();
        //var_dump($alert_list);die;
        if(!empty($alert_list))
        {
            foreach ($alert_list as $alert) {
                $alert = json_decode($alert, true);
                $uuid = $alert['log_id'];
                $alertData = [];
                $uniqueID = new UniqueID();
                $alertData['UUID'] = $uniqueID->get_uuid('bal');
                $alertData['LogUUID'] = $uuid;
                $alertData['Title'] = $alert['title'];
                $alertData['Content'] = $alert['content'];
                $alertData['CreatedTime'] = date('Y-m-d H:i:s', $alert['time']);
                $alertData['Status'] = 'Undo';

                $this->oMysql->insert('bo_alert_log', $alertData);
                $updateData = [];
                $updateData['Status'] = 'Done';
                if(!empty($uuid))
                {
                    $sql = "SELECT bcl.*, bct.Name, bct.AlertType,bct.TimeString,bct.TimeOutSecond FROM `bo_crontab_log` as bcl LEFT JOIN `bo_crontab` as bct ON bcl.CrontabUUID = bct.UUID WHERE bcl.UUID = '{$uuid}'";
                    $data = $this->oMysql->get_one($sql);
                    if(!empty($data))
                    {
                        if($data['AlertType'] == 'NOALERT')
                        {
                            $updateData['Level'] = 1;
                            $updateData['To'] = '';
                        }elseif($data['AlertType'] == 'EMAIL'){
                            $updateData['Level'] = 2;
                            //查找对应的alert users
                            $crontabUUID = $data['CrontabUUID'];
                            $sql = "SELECT bu.* FROM `bo_crontab_user` as bcu LEFT JOIN `bo_user` as bu ON bu.UUID = bcu.UserUUID WHERE bcu.CrontabUUID = '{$crontabUUID}'";
                            $alertUsers = $this->oMysql->get_all($sql);
                            if(!empty($alertUsers))
                            {
                                $toArr = [];
                                foreach ($alertUsers as $user) {
                                    $toArr[] = $user['Email'];
                                }
                                $updateData['To'] = implode(",", $toArr);
                            }else{
                                $updateData['To'] = To;
                            }

                            $list_name = 'cron_list_'.$crontabUUID;
                            $mail_list = $this->oRedis->rlist($list_name,1000);
                            $count = count($mail_list);
                            //var_dump($last_mail_list);die;
                            //var_dump($count);
                            $now = time();
                            if($count<=3) {

                                echo "email\n";
                                //$f = $this->error_reporting_via_email($alertData['Title'], $alertData['Content'], 'text', $updateData['To']);
                                $f = true;
                                if (!$f) {
                                    $updateData['Status'] = 'Failed';
                                    $list_name = 'alert_list';
                                    $this->oRedis->lpush($list_name, json_encode($alert));
                                } else {
                                    $logMail['cron_id'] = $crontabUUID;
                                    $logMail['status'] = 'send';
                                    $logMail['time'] = time();
                                    $this->push_to_cron_list($crontabUUID, json_encode($logMail));
                                }
                            }else{
                                $last_mail_list = json_decode($mail_list[$count-1], true);
                                if($now>$last_mail_list['time']+3600){

                                }
                                $logMail['cron_id'] = $mail['cron_id'];
                                $logMail['status'] = $mail['status'];
                                $logMail['time'] = $mail['time'];
                                $list_name = $logMail['log_id'];
                                $this->oRedis->lpush($list_name, json_encode($logMail));
                            }
                        }
                    }
                }
                $alertUUID = $alertData['UUID'];
                $where = "UUID = '{$alertUUID}'";
                $this->oMysql->update('bo_alert_log', $updateData, $where);
            }
        }
    }

    public function push_to_cron_list($crontabUUID,$str){
        $list_name = 'cron_list_'.$crontabUUID;
        $this->oRedis->lpush($list_name, $str);
    }

    public function pop_cron_list($crontabUUID){
        $list_name = 'cron_list_'.$crontabUUID;
        return $this->oRedis->lpop($list_name);
    }

    public function get_alert_list($size= 1000)
    {
        return $this->oRedis->rlist($this->redisKey, $size);
    }

    public function SendUtf8Html($to,$subject,$body="",$from="",$other_paras=array())
    {
        $allowed_charset = array("utf-8","iso-8859-1");
        $allowed_transfer_encoding = array("quoted-printable","base64");
        
        $charset = "utf-8";
        if(isset($other_paras["charset"]) && $other_paras["charset"]) $charset = $other_paras["charset"];
        if(!in_array($charset,$allowed_charset)) die("die: invalid charset: $charset\n");
            
        $transfer_encoding = "quoted-printable";//base64
        if(isset($other_paras["transfer_encoding"]) && $other_paras["transfer_encoding"]) $transfer_encoding = $other_paras["transfer_encoding"];
        if(!in_array($transfer_encoding,$allowed_transfer_encoding)) die("die: invalid transfer_encoding: $transfer_encoding\n");
        
        $headers = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/html; charset=$charset";
        $headers[] = "Content-Transfer-Encoding: $transfer_encoding";
        
        if($from) $headers[] = "From: $from";
        $headers[] = "Date: " . date("r");
        $headers[] = "X-Mailer: Mega Email Sender";
        
        list($msec, $sec) = explode(" ", microtime());
        $headers[] = "Message-ID: <" . date("YmdHis", $sec) . "." . ($msec*1000000) . "." . md5($from . "\t" . $subject . "\t" . $to) . ">";
        $str_header = implode("\r\n",$headers);
        
        if($transfer_encoding == "base64") $body = chunk_split(base64_encode($body));
        elseif($transfer_encoding == "quoted-printable") $body = quoted_printable_encode($body);
        
        if(isset($other_paras["encode_subject"]) && $other_paras["encode_subject"]) $subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
        return mail($to,$subject,$body,$str_header);
    }

    function error_reporting_via_email($subject,$body,$type="text",$to="")
    {
        $server_name = php_uname("n");
        $from = "root@" .  $server_name;
        if($type == "text")
        {
            $body = nl2br(htmlspecialchars($body));
        }
        return $this->SendUtf8Html($to,$subject,$body,$from);
    }
}