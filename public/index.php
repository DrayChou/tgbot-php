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
date_default_timezone_set(CFun::get_config('timezone', 'Asia/Shanghai'));

CFun::echo_log($_GET);
CFun::echo_log($_POST);

//如果有 token 带过来，那么调用对应的机器人
if(isset($_GET['token'])){
    CFun::set_config('token', $_GET['token']);

    //发调试信息
    $admins = CFun::get_config('admins');
    Telegram::singleton()->send_message(array(
        'chat_id' => -25936895,
        'text'    => json_encode($_POST),
    ));

    CFun::G('run_start');

    if (isset($_POST['message'])) {
        Process::run($_POST['message']);
    }

    CFun::G('run_end');
    $use_time = CFun::G('run_start', 'run_end');
    $use_mem  = CFun::G('run_start', 'run_end', 'm');
    $log = '耗时：' . $use_time . ' 耗内存：' . $use_mem . PHP_EOL . ' 当前占内存：' . CFun::convert_memory_size(memory_get_usage()) . PHP_EOL;

    CFun::echo_log($log);
}else{
    echo 'test' . PHP_EOL;
}