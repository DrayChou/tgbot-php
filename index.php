<?php

/**
 * User: dray
 * Date: 15/7/10
 * Time: 上午11:53
 */
define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
require_once(BASE_PATH . 'lib' . DIRECTORY_SEPARATOR . 'common.php');
CommonFunction::load_lib();

$http = new swoole_http_server("127.0.0.1", 9501);
$http->set(array('worker_num' => 4, 'daemonize' => true));

// 开启定时器
$timer_id = $http->tick(250, function ($id, $params) {
    CommonFunction::echo_log('开启定时器 id=%s parms=%s', $id, $params);

    // 回收运行结束的子进程
    $res = swoole_process::wait(true);
    CommonFunction::echo_log('回收子进程 $res=%s', $res);

    // 开启处理进程
    $process = new swoole_process(function ($process) {
        //接收数据
        $message = $process->read();
        Process::run($message);

        //退出进程
        $process->exit();
    });

    $message = Telegram::singleton()->post('getUpdates', array(
        'offset' => Db::get_update_id(),
//        'limit'  => 10,
    ));

    // 传入数据
    $process->write(json_encode($message));
    $pid = $process->start();
    CommonFunction::echo_log('开启子进程 id=%s', $pid);
}, null);

$http->on('request', function ($request, $response) {
    CommonFunction::echo_log('服务器信息: $messages=%s', $request);
    CommonFunction::echo_log('收到的请求信息: $messages=%s', $response);

    $response->end("<h1>Hello Swoole. #" . rand(1000, 9999) . "</h1>");
});
$http->start();
