<?php
/**
 * redis 操作类库
 * Class Db
 * @Author: dray
 * @Date:   2015-07-10 18:43:59
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-04 11:18:21
 */

class Db
{
    /**
     * 得到 redis 对象
     * @return Redis
     * @throws Exception
     */
    public static function get_redis()
    {
        $redis_config = Common::get_config('redis');
        if (empty($redis_config)) {
            $err = 'error redis config';
            Common::echo_log($err);
            Common::report_err($err);

            return null;
        }

        $redis = new Redis();
        $redis->connect($redis_config['ip'], $redis_config['port'], $redis_config['timeout']);

        return $redis;
    }

    /**
     * 缓存得到机器人自己的信息
     * @return array|bool|mixed|null|string
     * @throws Exception
     */
    public static function get_bot_info()
    {
        $token = Common::get_config('token');
        if (empty($token)) {
            $err = 'error token';
            Common::echo_log($err);
            Common::report_err($err);

            return null;
        }

        $key = BOT . ':' . (string) $token;
        $redis = self::get_redis();

        $bot_info = $redis->get($key);

        if (empty($bot_info)) {
            $bot_info = Telegram::singleton()->get_me();

            $redis->set($key, json_encode($bot_info));
        } else {
            $bot_info = json_decode($bot_info, true);
        }

        return $bot_info;
    }

    /**
     * 得到机器人的名字
     * @return string
     */
    public static function get_bot_name($join = ':')
    {
        $bot_info = self::get_bot_info();

        return BOT . $join . strtolower($bot_info['username']) . $join;
    }

    /**
     * 读取 update_id
     * @return int
     * @throws Exception
     */
    public static function get_update_id()
    {
        $key = self::get_bot_name() . 'update_id';
        $redis = self::get_redis();

        $id = (int) $redis->get($key);

        Common::echo_log('update_id:%s', $id);

        return $id;
    }

    /**
     * 设置 update_id
     * @param $id
     * @return int
     * @throws Exception
     */
    public static function set_update_id($id)
    {
        $key = self::get_bot_name() . 'update_id';
        $redis = self::get_redis();

        return (int) $redis->set($key, $id, 3600);
    }

    /**
     * 缓存替换好的路由规则
     * @return bool|mixed|string
     * @throws Exception
     */
    public static function get_router($use_cache = true)
    {
        $key = self::get_bot_name() . 'config:router';
        $redis = self::get_redis();
        $router = $redis->get($key);
        if ($use_cache == false || empty($router)) {
            $router = Common::get_router();

            $redis->setex($key, 3600, json_encode($router));
        } else {
            $router = json_decode($router, true);
        }

        Common::echo_log('router:%s', print_r($router, true));

        return $router;
    }

    /**
     * 设置某些值
     * @param $key
     * @param $val
     * @param null $time
     * @return bool
     * @throws Exception
     */
    public static function set($key, $val, $time = null)
    {
        $key = self::get_bot_name() . $key;
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
    public static function get($key)
    {
        $key = self::get_bot_name() . $key;
        $redis = self::get_redis();

        $val = $redis->get($key);
        if (is_string($val)) {
            $val = json_decode($val);
        }

        return $val;
    }
}
