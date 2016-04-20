<?php

/**
 * User: dray
 * Date: 15/7/10
 * Time: 上午11:53
 */

define('BOT', 'tgbot-php');
define('BASE_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('LIB_PATH', BASE_PATH . 'lib' . DIRECTORY_SEPARATOR);

//加载包文件
require_once(LIB_PATH . 'process.php');

//设置时区
date_default_timezone_set(Common::get_config('timezone', 'Asia/Shanghai'));

//如果有 token 带过来，那么调用对应的机器人
if (isset($_GET['token'])) {

    // 设置 token
    Common::set_config('token', $_GET['token']);

    //设置日志
    ini_set("display_errors", 0);
    if ($log_path = Common::get_config('log_path')) {
        ini_set("error_reporting", E_ALL);
        ini_set("error_log", $log_path . Db::get_bot_name() . '.log');
        ini_set("log_errors", 1);
    }

    Common::G('run_start');

    //接收数据，并处理
    $input = file_get_contents('php://input');
    Process::run(array($input));

    Common::G('run_end');
    $log = '耗时：' . Common::G('run_start', 'run_end') . ' 当前占内存：' . Common::convert_memory_size(memory_get_usage()) . PHP_EOL;
    Common::echo_log($log);
} else {
    echo 'This is ' . BOT . '!' . PHP_EOL;
}
