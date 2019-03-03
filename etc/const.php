<?php
error_reporting(E_ALL);
set_time_limit(86400);

umask(0022); //default permission 644
define("DEBUG_MODE", false);

$server_name = php_uname("n");
list($short_server_name) = explode(".",$server_name);

define("SERVER_NAME",$server_name);
define("SHORT_SERVER_NAME",$short_server_name);

define("TIME_ZONE", "America/Los_Angeles");
define("MYSQL_TIME_ZONE", "America/Los_Angeles");
date_default_timezone_set(TIME_ZONE);

define("INCLUDE_ROOT", dirname(dirname(__FILE__))."/");
define("/", dirname(INCLUDE_ROOT) . "/");
define("PUBLIC_ROOT", INCLUDE_ROOT . "public/");
define("API_ROOT", PUBLIC_ROOT . "api/");
define("LOG_DIR", INCLUDE_ROOT . "logs/");
define("DATA_DIR", INCLUDE_ROOT . "data/");

define("PROD_DB_HOST", "192.168.1.10");
define("PROD_DB_USER", "mark");
define("PROD_DB_PASS", "vlvsTPeG");
define("PROD_DB_NAME", "test_phpcron");
define("PROD_DB_PORT", 3306);

define("REDIS_HOST", "127.0.0.1");
define("REDIS_PORT", "6379");
define("REDIS_PASS", "");
define("REDIS_PREFIX", "phpcron");
define("REDIS_TIMEOUT", "30");

define("TO", "markchu@meikaitech.com");
function __autoload($class)
{
    $class_file = INCLUDE_ROOT . "lib/class.{$class}.php";
    if(file_exists($class_file)) include_once($class_file);
}

function mydie($str="")
{
    if($str) echo $str;
    exit(1);
}