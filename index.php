<?php
/**
 * User: dray
 * Date: 15/7/10
 * Time: 上午11:53
 */

$http = new swoole_http_server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    var_dump($request);
    var_dump($response);

    $response->end("<h1>Hello Swoole. #" . rand(1000, 9999) . "</h1>");
});
$http->start();