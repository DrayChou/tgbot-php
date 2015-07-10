<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Redis {

    /**
     * 得到 redis 对象
     * @return Redis
     * @throws Exception
     */
    static function get_redis() {
        $config = CommonFunction::get_config();
        if (empty($config) || empty($config['redis'])) {
            throw new Exception('error redis config');
        }

        $redis_config = $config['redis'];

        $redis = new Redis();
        $redis->connect($redis_config['ip'], $redis_config['port'], $redis_config['timeout']);

        return $redis;
    }

    /**
     * 读取 update_id
     * @return int
     * @throws Exception
     */
    static function get_update_id() {
        $config = CommonFunction::get_config();
        if (empty($config) || empty($config['bot_name'])) {
            throw new Exception('error bot_name');
        }

        $bot   = $config['bot_name'];
        $key   = $bot . ':' . 'update_id';
        $redis = self::get_redis();

        $id = (int) $redis->get($key);

        CommonFunction::echo_log('update_id:%s', $id);

        return $id;
    }

    /**
     * 设置 update_id
     * @param $id
     * @return int
     * @throws Exception
     */
    static function set_update_id($id) {
        $config = CommonFunction::get_config();
        if (empty($config) || empty($config['bot_name'])) {
            throw new Exception('error bot_name');
        }

        $bot   = $config['bot_name'];
        $key   = $bot . ':' . 'update_id';
        $redis = self::get_redis();

        return (int) $redis->set($key, $id);
    }
}
