<?php
class PhpCrontab
{
	/*
		https://github.com/jkonieczny/PHP-Crontab
		min (0 - 59) | hour (0 - 23) | day of month (1 - 31) | month (1 - 12) | day of week (0 - 6) (Sunday=0) | Command
	*/
	public static function parse($_cron_time,$_after_timestamp=null)
	{
		$pattern = '/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i';
		if(!preg_match($pattern,trim($_cron_time)))
		{
			throw new InvalidArgumentException("Invalid cron string: ".$_cron_time);
		}

		if($_after_timestamp && !is_numeric($_after_timestamp))
		{
			throw new InvalidArgumentException("\$_after_timestamp must be a valid unix timestamp ($_after_timestamp given)");
		}
		$cron = preg_split("/[\s]+/i",trim($_cron_time));
		$start = $_after_timestamp ? $_after_timestamp : time();

		$date = array(
					'minutes' =>self::_parseCronNumbers($cron[0],0,59),
					'hours' =>self::_parseCronNumbers($cron[1],0,23),
					'dom' =>self::_parseCronNumbers($cron[2],1,31),
					'month' =>self::_parseCronNumbers($cron[3],1,12),
					'dow' =>self::_parseCronNumbers($cron[4],0,6),
				);

		// limited to time()+366 - no need to check more than 1year ahead
		for($i=0;$i<=3600*24*366;$i+=60)
		{
			if(in_array(intval(date('j',$start+$i)),$date['dom']) &&
				in_array(intval(date('n',$start+$i)),$date['month']) &&
				in_array(intval(date('w',$start+$i)),$date['dow']) &&
				in_array(intval(date('G',$start+$i)),$date['hours']) &&
				in_array(intval(date('i',$start+$i)),$date['minutes']))
			{
				return $start+$i;
			}
		}
		return false;
	}
	public static function isCorrectCronTime($_cron_time)
	{
		$pattern = '/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i';
		return preg_match($pattern,trim($_cron_time));
	}
	protected static function _parseCronNumbers($s,$min,$max)
	{
		$result = array();
		$v = explode(',',$s);
		foreach($v as $vv)
		{
			$vvv = explode('/',$vv);
			$step = empty($vvv[1])?1:$vvv[1];
			$vvvv = explode('-',$vvv[0]);
			$_min = count($vvvv)==2?$vvvv[0]:($vvv[0]=='*'?$min:$vvv[0]);
			$_max = count($vvvv)==2?$vvvv[1]:($vvv[0]=='*'?$max:$vvv[0]);

			for($i=$_min;$i<=$_max;$i+=$step)
			{
				$result[$i]=intval($i);
			}
		}
		ksort($result);
		return $result;
	}
 }//end class
 ?>