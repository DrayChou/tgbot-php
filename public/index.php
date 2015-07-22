<?php

/**
 * User: dray
 * Date: 15/7/10
 * Time: 上午11:53
 */

define('BOT', 'tgbot-php');
define('BASE_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('LIB_PATH', BASE_PATH . 'lib' . DIRECTORY_SEPARATOR);
define('INTERVAL', 1);// 每隔 xx 毫秒运行

ini_set('memory_limit', '32M');
ignore_user_abort();//关掉浏览器，PHP脚本也可以继续执行.
set_time_limit(0);// 通过set_time_limit(0)可以让程序无限制的执行下去

//加载包文件
require_once(LIB_PATH . 'process.php');

//设置时区
date_default_timezone_set(CFun::get_config('timezone', 'Asia/Shanghai'));

if (empty($_POST)) {
    echo 'test' . PHP_EOL;
} else {

    //发调试信息
    $admins = CFun::get_config('admins');
    Telegram::singleton()->send_message(array(
        'chat_id' => -25936895,
        'text'    => json_encode($_POST),
    ));

    //死循环查询
//    do {
    CFun::G('run_start');

    Process::run($_POST['message']);

    CFun::G('run_end');
    $use_time = CFun::G('run_start', 'run_end');
    $use_mem  = CFun::G('run_start', 'run_end', 'm');
    echo '耗时：' . $use_time . ' 耗内存：' . $use_mem . PHP_EOL . ' 当前占内存：' . CFun::convert_memory_size(memory_get_usage()) . PHP_EOL;

//        usleep(INTERVAL);// 等待
//    } while (true);
}
