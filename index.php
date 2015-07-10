<?php
/**
 * User: dray
 * Date: 15/7/10
 * Time: 上午11:53
 */

define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
require_once(BASE_PATH . 'lib' . DIRECTORY_SEPARATOR . 'common.php');
require_once(BASE_PATH . 'lib' . DIRECTORY_SEPARATOR . 'process.php');
require_once(BASE_PATH . 'lib' . DIRECTORY_SEPARATOR . 'telegram.php');

$config = require(BASE_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php');
echo_log('配置信息: $messages=%s', $config);

$http = new swoole_http_server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    echo_log('服务器信息: $messages=%s', $request);
    echo_log('收到的请求信息: $messages=%s', $response);

    $process = new swoole_process(function ($process) {
        $message = $process->read();
        Process::run($message);
    });

    $message = Telegram::singleton()->post('getUpdates', array(
        'offset' => get_update_id(),
//        'limit'  => 10,
    ));

    $process->write(json_encode($message));
    $pid = $process->start();

    $response->end("<h1>Hello Swoole. #" . rand(1000, 9999) . "</h1>");
});
$http->start();