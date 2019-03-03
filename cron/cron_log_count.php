<?php 
/**
 * 按天/周/月/统计各个脚本的执行情况，生成excel表格并发出邮件
 *
 */
if(isset($_SERVER["REQUEST_METHOD"])) die("please run this script from CLI.");
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(dirname(dirname(__FILE__)) . "/lib/phpexcel/Classes/PHPExcel.php");
include_once(dirname(dirname(__FILE__)) . "/lib/phpmailer/PHPMailerAutoload.php");
define("PAGE_DEBUG",false);

$oMysql = new BoDb();

$dateList = ['daily','weekly','monthly'];
//$argv[1] = 'weekly';
if(!isset($argv[1])||!in_array($argv[1],$dateList)){
    echo 'request error';die;
}
$end = date('Y-m-d H:i:s');
if($argv[1] == 'weekly'){
    $start = date('Y-m-d 00:00:00',strtotime($end."-6 day"));
}elseif($argv[1] == 'monthly'){
    $start = date('Y-m-01 00:00:00');
}elseif($argv[1] == 'daily'){
    $start = date('Y-m-d 00:00:00');
}

//$sql = "SELECT bc.UUID,bc.Name,bc.CommandString,bc.Server,bcl.ProcessStatus FROM `bo_crontab` as bc left join `bo_crontab_log` as bcl on bcl.CrontabUUID=bc.UUID where StartTime>'{$start}' and EndTime<'{$end}'";
$sql = "SELECT bc.UUID,bc.Name,bc.CommandString,bcl.ProcessStatus FROM `bo_crontab` as bc left join `bo_crontab_log` as bcl on bcl.CrontabUUID=bc.UUID where StartTime>'{$start}' and EndTime<'{$end}'";
$cronList = $oMysql->get_all($sql);
$logCount = [];
foreach($cronList as $k=>$v){
    $logCount[$v['UUID']]['Name'] = $v['Name'];
    //$logCount[$v['UUID']]['Server'] = $v['Server'];
    $logCount[$v['UUID']]['Server'] = 'be01';
    $logCount[$v['UUID']]['CommandString'] = $v['CommandString'];
    $logCount[$v['UUID']]['Total'] = isset($logCount[$v['UUID']]['Total'])?($logCount[$v['UUID']]['Total']+1):1;
    $logCount[$v['UUID']]['SUCC'] = isset($logCount[$v['UUID']]['SUCC'])?$logCount[$v['UUID']]['SUCC']:0;
    $logCount[$v['UUID']]['FAILED'] = isset($logCount[$v['UUID']]['FAILED'])?$logCount[$v['UUID']]['FAILED']:0;
    if($v['ProcessStatus'] == 'SUCC'){
        $logCount[$v['UUID']]['SUCC'] = $logCount[$v['UUID']]['SUCC']+1;;
    }
    if($v['ProcessStatus'] == 'FAILED'){
        $logCount[$v['UUID']]['FAILED'] = $logCount[$v['UUID']]['FAILED']+1;
    }
}
$failedList = [];
foreach($logCount as $k=>$v){
    if($v['FAILED']>0){
        $failedList[$k] = $v;
        unset($logCount[$k]);
    }
}
$logCount = array_merge($failedList,$logCount);
$totalCrontab = count($logCount);
$failedCount = count($failedList);
if($logCount){
    if($argv[1]=='daily'){
        $dateRange = date("Ymd");
    }else{
        $dateRange = date("Ymd",strtotime($start)).'~'.date("Ymd");
    }

    $body = "<table border=\"1\">\n";
    $body .= "<caption>cron logs report {$argv[1]}({$dateRange})</caption>\n";
    $body .= "<tr><th>Name</th><th>Server</th><th>CommandString</th><th>Total</th><th>SUCC</th><th>FAILED</th></tr>\n";
    foreach($logCount as $row)
    {
        echo $row["Name"] . "\t" . $row["Server"] . "\t" . $row["CommandString"] . "\t" . $row["Total"] . "\t" . $row["SUCC"] . "\t" . $row["FAILED"] . "\n";
        if($row['FAILED']>0){
            $body .= "<tr bgcolor=''><td>" . $row["Name"] . "</td><td>" . htmlspecialchars($row["Server"]) . "</td><td>" . htmlspecialchars($row["CommandString"]) . "</td><td>" . htmlspecialchars($row["Total"]) . "</td><td>" . htmlspecialchars($row["SUCC"]) . "</td><td>" . nl2br(htmlspecialchars($row["FAILED"])) . "</td></tr>\n";
        }else{
            $body .= "<tr><td>" . $row["Name"] . "</td><td>" . htmlspecialchars($row["Server"]) . "</td><td>" . htmlspecialchars($row["CommandString"]) . "</td><td>" . htmlspecialchars($row["Total"]) . "</td><td>" . htmlspecialchars($row["SUCC"]) . "</td><td>" . nl2br(htmlspecialchars($row["FAILED"])) . "</td></tr>\n";
        }
    }
    $body .= "</table>\n";
    $body .= "<span>You can click on the link below to see the details =>></span>";
    $body .= "<span><a href='http://www.baidu.com'>show details</a></span>";

    $subject = " cron log ".$argv[1]." report(totalCrontab:{$totalCrontab},failedCrontab:{$failedCount}) create by ".date ( "Y-m-d H:i:s" ); //邮件标题
    //$users = [explode('@',TO)[0]=>TO];
    $users = [
        //'markchu' => 'markchu@meikaitech.com',
        'paynexu' => 'paynexu@meikaitech.com',
    ];
    PHPMailer_Send($users,$subject,$body,true,'');
}


function PHPMailer_Send(array $user, $subject="",$body="",$isHTML=false,$attchment ="") {
    $mail = new PHPMailer (); //建立邮件发送类
    $mail->IsSMTP (); // 使用SMTP方式发送
    $mail->CharSet = 'UTF-8'; // 设置邮件的字符编码
    $mail->Host = "localhost"; // 您的企业邮局域名
    $mail->SMTPAuth = false; // 启用SMTP验证功能
    //$mail->Username = "user@xxxx.com"; // 邮局用户名(请填写完整的email地址)
    //$mail->Password = "******"; // 邮局密码
    $mail->From = "root@be01.bwe.io"; //邮件发送者email地址
    $mail->FromName = "root";

    foreach ($user as $name => $email) {
        $mail->AddAddress ($email, $name); //收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
    }

    //$mail->AddReplyTo("", "");
    if ($attchment) {
        $mail->AddAttachment ( $attchment ); // 添加附件
    }
    $mail->IsHTML($isHTML); // set email format to HTML //是否使用HTML格式
    $mail->Subject = $subject; //邮件标题
    $mail->Body = $body; //邮件内容:
    //$mail->AltBody = "This is the body in plain text for non-HTML mail clients"; //附加信息，可以省略
    if (! $mail->Send ()) {
        echo "Mail Send Failure. <p>\n";
        echo "ErrorInfo: " . $mail->ErrorInfo;
    } else {
        echo "Mail Send Successfully.\n<br>";
    }
}




