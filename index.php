<?php

/**
 * User: dray
 * Date: 15/7/10
 * Time: 上午11:53
 */

ini_set('memory_limit', '32M');

define('BOT', 'tgbot-php');
define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('LIB_PATH', BASE_PATH . 'lib' . DIRECTORY_SEPARATOR);
require_once(LIB_PATH . 'process.php');

//设置时区
date_default_timezone_set(CFun::get_config('timezone', 'Asia/Shanghai'));

// 定义服务
$http = new swoole_http_server("127.0.0.1", 9501);
$http->set(array(
    'daemonize'         => 1,
    'worker_num'        => 2,
    'max_conn'          => 10,
    'reactor_num'       => 1,
    'max_request'       => 10,
    'open_cpu_affinity' => 1,
));

// 开启定时器
$http->tick(1000, function () {
    Process::run();
});

// 接收到的请求数据
$http->on('request', function ($request, $response) {
    CFun::echo_log('服务器信息: $messages=%s', $request);
    CFun::echo_log('收到的请求信息: $messages=%s', $response);

    $response->end("<h1>Hello Swoole. #" . rand(1000, 9999) . "</h1>");
});

$http->start();
