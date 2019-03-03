<?php
class BoCrontab
{
	public $file_crontab = null;
	public $oMysql;
	public $oRedis;
	public $debug = false;
	public $verbose = false;
	public $crontab_source_type = "mysql";
	public $other_col_names = array("CommandString","LogFileString","UUID","Name","TimeOutSecond","AlertType");
	public $username = "";
	public function __construct($oMysql, $oRedis, $debug=null, $verbose=null)
	{
		$this->file_crontab = DATA_DIR . "crontab.txt";
		$this->oMysql = $oMysql;
		$this->oRedis = $oRedis;
		$this->username = get_current_user();
		if(isset($debug)) $this->debug = $debug;
		if(isset($verbose)) $this->verbose = $verbose;
	}

	function save_file()
	{
		$lines = array();
		$lines[] = "#created time:" . date("c") . "\n";
		$rows = $this->get_crontab_array("mysql");
		foreach($rows as $row)
		{
			$other_cols = array();
			foreach($this->$other_col_names as $name)
			{
				$val = isset($row[$name]) ? str_replace(array("\r\n\t")," ",$row[$name]) : "";
				$other_cols[] = $val;
			}

			$line = $row["TimeString"] . " " .implode("\t",$other_cols) . "\n";
			$lines[] = $line;
		}

		if(!is_writeable(DATA_DIR))
		{
			throw new Exception("no permission to write file {$this->file_crontab}");
		}

		$res = file_put_contents($this->file_crontab,$lines);
		if($res) @chmod($this->file_crontab, 0644);
		return $res;
	}

	function get_file_crontab_lines($renew=false)
	{
		if($renew || !file_exists($this->file_crontab))
		{
			$this->save_file();
			if(!file_exists($this->file_crontab))
			{
				return false;
			}
		}
		return file($this->file_crontab);
	}

	function get_crontab_array($crontab_source_type="")
	{
		if(!$crontab_source_type) $crontab_source_type = $this->crontab_source_type;
		 if($crontab_source_type == "mysql")
		 {
			$sql = "select * from bo_crontab where IsActive = 'YES' and `UserName` = '" . addslashes($this->username) . "' order by CommandString";
			//if($this->debug || $this->verbose) echo "sql: $sql\n";
			$rows = $this->oMysql->get_all($sql);
			return $rows;
		 }
		 elseif($crontab_source_type == "file")
		 {
			$rows = array();
			$lines = $this->get_file_crontab_lines();
			foreach ($lines as $line)
			{
				$row = array();
				$slices = preg_split("/[\s]+/", $line, 6);
				if(count($slices) !== 6 ) continue;
				if(substr($slices[0],0,1) == "#")  continue;
				$other_col_string = array_pop($slices);
				$cron_time = implode(' ',$slices);
				$row["TimeString"] = $cron_time;
				$other_cols = explode("\t",$other_col_string);
				foreach($this->$other_col_names as $i => $name)
				{
					if(isset($other_cols[$i])) $row[$name] = $other_cols[$i];
				}
				$rows[] = $row;
			}
			return $rows;
		 }

	 	 throw new Exception("Invalid crontab_source_type {$this->crontab_source_type}");
	}

	function run_crontab($timestamp=null)
	{
		$childs = array();
		if(!$timestamp) $timestamp = time();
		$rows = $this->get_crontab_array();
		if(empty($rows))  return false;
		foreach($rows as $row)
		{
			$next_time = PhpCrontab::parse($row["TimeString"],$timestamp);
			if($next_time !== $timestamp) continue;

			$row["CommandString"] = $this->replace_vars($row["CommandString"],$row);
  			$row["LogFileString"] = $this->replace_vars($row["LogFileString"],$row);

			$pid = pcntl_fork();
  			if($pid == -1)
  			{
   				die("could not fork");
  			}
  			elseif($pid)
  			{
				//pcntl_wait($status, WNOHANG); //Protect against Zombie children
				$childs[$pid] = $row;
				if($this->verbose) echo "parent has child pid=$pid UUID=" . $row["UUID"] . " Command=" . $row["CommandString"] . " LogFile=" . $row["LogFileString"] .  "\n";
  			}
  			else
  			{
				if($this->verbose)
  				{
  					echo date("c") . " child exec: UUID=" . $row["UUID"] . " Command=" . $row["CommandString"] . " LogFile=" . $row["LogFileString"] .  "\n";
  				}

  				$row["ProcessId"] = posix_getpid();
  				$log_id = $this->do_log($row);
  				$arr_update = array();
  				$start_time = microtime(true);
  				$run_arr = [
  				    'pid' => $row["ProcessId"],
  				    'cron_id' => $row["UUID"],
  				    'log_id' => $log_id,
  				    'start_time' => $start_time,
  				    'time_out' => $row["TimeOutSecond"],
  				    'command' => $row["CommandString"]
  				];
  				$this->push_to_run_list($timestamp, json_encode($run_arr));
 				$ret_val = $this->exec($row["CommandString"],$row["LogFileString"]);
 				if($ret_val > 0) $arr_update["ProcessStatus"] = "FAILED";
 				elseif($ret_val < 0) $arr_update["ProcessStatus"] = "LOST";
 				else $arr_update["ProcessStatus"] = "SUCC";
 				$content_length = 100;
 				if($arr_update["ProcessStatus"] != "SUCC")
				{
					$alert = [
						'log_id' => $log_id,
						'title' => '该进程没有完成，状态为'.$arr_update["ProcessStatus"],
						'content' => $row["CommandString"]. ' 命令运行失败',
						'time' => time()
					];
					$this->push_to_alert_list(json_encode($alert));
					$content_length = 1000;
				}else{
					if(!empty($row['SuccFlag']))
					{
						$file = escapeshellarg($row["LogFileString"]);
						$line = `tail -n1 $file`;
						if(stripos($line, trim($row["SuccFlag"])) === false)
						{
							$alert = [
								'log_id' => $log_id,
								'title' => $row['Name'].' 没有检测到期望输出: '.$row['SuccFlag'],
								'content' => $row["CommandString"]. ' 命令运行失败',
								'time' => time()
							];
							$arr_update["ProcessStatus"] = "FAILED";
							$this->push_to_alert_list(json_encode($alert));
						}
					}
				}
 				$arr_update["LogContent"] = $this->get_file_tail($row["LogFileString"], $content_length);
 				$arr_update["EndTime"] = date("Y-m-d H:i:s");

 				if($log_id) $this->update_log($log_id,$arr_update);
				exit;
  			}
		}
		$done_process = [];
		while(count($childs) > 0)
		{
			$list_count = $this->get_run_list_length($timestamp);
			if($list_count > 0)
			{
				for ($i=0; $i < $list_count; $i++) {
					$run_cron = $this->pop_run_list($timestamp);
					$run_cron_arr = json_decode($run_cron, true);
					$now = microtime(true);
					$pid = $run_cron_arr['pid'];
					$start_time = $run_cron_arr['start_time'];
					$time_out = $run_cron_arr['time_out'];
					$time_diff = (($now - $start_time) > $time_out);
					if($time_out > 0 && $time_diff )
					  {
						if(!isset($run_cron_arr['out_flag']))
						{
									$alert = [
										'log_id' => $run_cron_arr['log_id'],
										'title' => 'cron time out',
										'content' => $run_cron_arr['command'].' is time out',
										'time' => time()
									 ];
									 $run_cron_arr['out_flag'] = 'yes';
										$this->push_to_alert_list(json_encode($alert));
								}
						}

					$res = pcntl_waitpid($pid, $status, WNOHANG);
					// If the process has already exited
					if($res == -1 || $res > 0)
					{
						$done_process[] = $run_cron_arr;
						unset($childs[$pid]);
					}
					else{
						$this->push_to_run_list($timestamp, json_encode($run_cron_arr));
					}
				}
			}
			sleep(2);
		}
		// 子进程全部退出，然后检查log表中的状态，若进程退出，然是log表中的状态还是running
		if(!empty($done_process))
		{
			foreach ($done_process as $cron) {
				$status = $this->run_status($cron['log_id']);
				if($status == 'RUNNING')
				{
					$alert = [
						'log_id' => $cron['log_id'],
						'title' => '该进程没有完成',
						'content' => $cron['command']. ' 命令运行没有完成!',
						'time' => time()
					];
					$this->push_to_alert_list(json_encode($alert));
				}
			}
		}
	}
	function push_to_run_list($timestamp, $str)
	{
		$list_name = $this->username.'_run_list_'.$timestamp;
		$this->oRedis->lpush($list_name, $str);
	}
	function pop_run_list($timestamp)
	{
		$list_name = $this->username.'_run_list_'.$timestamp;
		return $this->oRedis->rpop($list_name);
	}
	function get_run_list_length($timestamp)
	{
		$list_name = $this->username.'_run_list_'.$timestamp;
		return $this->oRedis->lsize($list_name);
	}
	function push_to_alert_list($str)
	{
		$list_name = 'alert_list';
		$this->oRedis->lpush($list_name, $str);
	}
	function run_status($log_id)
	{
		$sql = "SELECT ProcessStatus From `bo_crontab_log` WHERE UUID = '{$log_id}'";
		$result = $this->oMysql->get_one($sql);
		return empty($result)? false: $result['ProcessStatus'];

	}
	function get_file_tail($logfile,$content_length=100)
	{
		if(!is_readable($logfile)) return "";
		$content = "";

		$filesize = filesize($logfile);
		if($filesize == 0) return "";
		elseif($filesize <= $content_length) $content_length = $filesize;
		else $content = "... ";

		if(!$fp=fopen($logfile,'r')) return "";
		if(fseek($fp, 0 - $content_length,SEEK_END) != 0) return "";
		$content .= fread($fp, $content_length);
		fclose($fp);
		return $content;
	}

	function replace_vars($cmd,$row)
	{
		$user_defined_runtime_vars = array();
		$user_defined_runtime_vars["UDRV_TIMESTAMP"] = time();
		$user_defined_runtime_vars["UDRV_DATE_TIME"] = date("YmdHis",$user_defined_runtime_vars["UDRV_TIMESTAMP"]);
		$user_defined_runtime_vars["UDRV_DATE"] = substr($user_defined_runtime_vars["UDRV_DATE_TIME"],0,8);
		$user_defined_runtime_vars["UDRV_TIME"] = substr($user_defined_runtime_vars["UDRV_DATE_TIME"],-6);
		$user_defined_runtime_vars["UDRV_UUID"] = $row["UUID"];
		$user_defined_runtime_vars["UDRV_NAME"] = $row["Name"];

		$arr_replace_from = array();
		$arr_replace_to = array();
		$arr_all_var = array();

		$pattern = "|\\{([A-Z0-9_]+)\\}|";
		if(preg_match_all($pattern,$cmd,$out, PREG_PATTERN_ORDER))
		{
			foreach($out[1] as $var)
			{
				$arr_all_var[$var] = $var;
			}
		}

		foreach($arr_all_var as $var)
		{
			$arr_replace_from[] = "{" . $var . "}";
			if(isset($user_defined_runtime_vars[$var]))
			{
				$arr_replace_to[] = $user_defined_runtime_vars[$var];
			}
			elseif(defined($var))
			{
				$arr_replace_to[] = constant($var);
			}
			else
			{
				//error
				$arr_replace_to[] = "ERROR_VAR_" . $var;
			}
		}

		if(sizeof($arr_replace_from))
		{
			$cmd = str_replace($arr_replace_from,$arr_replace_to,$cmd);
		}

		return $cmd;
	}

	function exec($cmd,$logfile="")
	{
		if($logfile && !is_writable($logfile))
		{
			$logdir = dirname($logfile);
			if(!is_dir($logdir)) @mkdir($logdir,0777,true);
			@touch($logfile);
			if(!is_writable($logfile)) $logfile = "";
		}

		if($logfile)
		{
			$descriptorspec = array(
				0 => array("pipe", "r"),
				1 => array("file", $logfile, "a"),
				2 => array("file", $logfile, "a")
			);
		}
		else
		{
			//0:STDIN 1:STDOUT 2:STDERR
			$descriptorspec = array(
				0 => array("pipe", "r"),
				1 => array("pipe", "w"),
				2 => array("pipe", "w")
			);
		}

		$exit_code = -1;
		$process = proc_open($cmd,$descriptorspec,$pipes);
		if (is_resource($process))
		{
			for($i=0;$i<3;$i++)
			{
				if(isset($pipes[$i]) && is_resource($pipes[$i])) fclose($pipes[$i]);
			}

			// 切记：在调用 proc_close 之前关闭所有的管道以避免死锁。
			//0: succ 1:error -1:the external program has exited on its own before
			$exit_code = proc_close($process);
		}
		return $exit_code;
	}

	function do_log($row)
	{
		$oUUID = new UniqueID();
		$uuid = $oUUID->get_uuid("bcl");
		$start_time = date('Y-m-d H:i:s');
		$sql = "INSERT INTO `bo_crontab_log` (`UUID`, `CrontabUUID`, `Name`, `LogFileString`, `CommandString`, `ProcessStatus`, `StartTime`, `EndTime`, `LogContent`, `ProcessId`,`UserName`) VALUES  ('$uuid', '" . $row["UUID"] . "', '" . $row["Name"] . "', '" . $row["LogFileString"] . "', '" . addslashes($row["CommandString"]) . "', 'RUNNING', '{$start_time}', null, '', '" . $row["ProcessId"] . "','" . addslashes($this->username) . "')";
		if($this->debug || $this->verbose) echo "sql: $sql\n";
		if(!$this->debug) $this->oMysql->query($sql);
		return $uuid;
	}

	function update_log($log_id,$arr_update)
	{
		$arr = array();
		foreach($arr_update as $name => $v)
		{
			$arr[] = "`$name` = '" . addslashes($v) . "'";
		}
		$sql = "update `bo_crontab_log`  set " . implode(",",$arr) . " where UUID = '$log_id'";
		if($this->debug || $this->verbose) echo "sql: $sql\n";
		if(!$this->debug) $this->oMysql->query($sql);
	}
}
?>