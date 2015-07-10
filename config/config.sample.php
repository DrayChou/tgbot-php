<?php
/**
 * 配置信息
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:16
 */

return array(
    //机器人的 token
    'token'    => '',
    //机器人的名称，一般用来作为 redis 的key
    'bot_name' => '',

    //redis 配置
    'redis'    => array(
        'ip'      => '127.0.0.1',
        'port'    => 6379,
        'timeout' => 2.5,
    ),

    //管理员配置信息
    'admins'   => array(
        0,
    ),
);