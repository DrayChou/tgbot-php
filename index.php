<?php
/**
 * User: dray
 * Date: 15/7/10
 * Time: 上午11:53
 */

define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
require_once(BASE_PATH . 'lib' . DIRECTORY_SEPARATOR . 'process.php');
require_once(BASE_PATH . 'lib' . DIRECTORY_SEPARATOR . 'telegram.php');

$config = require(BASE_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php');
var_dump($config);

$http = new swoole_http_server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    var_dump($request);
    var_dump($response);

    $process = new swoole_process(function ($process) {
        $message = $process->read();
        Process::run($message);
    });

    $message = Telegram::singleton()->post('getUpdates', array(
        'offset' => 76,
//        'limit'  => 10,
    ));

    var_dump($message);

    $process->write(json_encode($message));
    $pid = $process->start();

    $response->end("<h1>Hello Swoole. #" . rand(1000, 9999) . "</h1>");
});
$http->start();