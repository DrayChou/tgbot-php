<?php

/**
 * redis 操作类库
 * Class Db
 */
class Db
{

    /**
     * 得到 redis 对象
     * @return Redis
     * @throws Exception
     */
    static function get_redis() {
        $redis_config = CFun::get_config('redis');
        if (empty($redis_config)) {
            $err = 'error redis config';
            CFun::echo_log($err);
            CFun::report_err($err);

            return NULL;
        }

        $redis = new Redis();
        $redis->connect($redis_config['ip'], $redis_config['port'], $redis_config['timeout']);

        return $redis;
    }

    /**
     * 得到机器人的名字
     * @return string
     */
    static function get_bot_name() {
        $bot_info = Telegram::singleton()->get_me();

        return strtolower($bot_info['username']) . ':';
    }

    /**
     * 读取 update_id
     * @return int
     * @throws Exception
     */
    static function get_update_id() {
        $key   = self::get_bot_name() . 'update_id';
        $redis = self::get_redis();

        $id = (int)$redis->get($key);

        CFun::echo_log('update_id:%s', $id);

        return $id;
    }

    /**
     * 设置 update_id
     * @param $id
     * @return int
     * @throws Exception
     */
    static function set_update_id($id) {
        $key   = self::get_bot_name() . 'update_id';
        $redis = self::get_redis();

        return (int)$redis->set($key, $id);
    }

    /**
     * 设置某些值
     * @param $key
     * @param $val
     * @param null $time
     * @return bool
     * @throws Exception
     */
    static function set($key, $val, $time = NULL) {
        $key   = self::get_bot_name() . $key;
        $redis = self::get_redis();

        if (!is_string($val)) {
            $val = json_encode($val);
        }

        if (is_numeric($time)) {
            if ($time > 0) {
                return $redis->setex($key, $time, $val);
            } else {
                return $redis->del($key);
            }
        } else {
            return $redis->set($key, $val);
        }
    }

    /**
     * 返回某些值
     * @param $key
     * @return bool|string
     * @throws Exception
     */
    static function get($key) {
        $key   = self::get_bot_name() . $key;
        $redis = self::get_redis();

        $val = $redis->get($key);
        if (is_string($val)) {
            $val = json_decode($val);
        }

        return $val;
    }
}
