<?php

/**
 * 配置信息
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:16
 */
return array(
    //机器人的 token
    'token'  => '',

    //图灵机器人的配置信息
    'tuling' => array(
        'key' => '4d546ffb4cd27187ef2a20d3af54c5b6',
    ),

    //redis 配置
    'redis'  => array(
        'ip'      => '127.0.0.1',
        'port'    => 6379,
        'timeout' => 2.5,
    ),

    //管理员配置信息
    'admins' => array(
        0,
    ),
);
