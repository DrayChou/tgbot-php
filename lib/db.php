<?php

/**
 * redis 操作类库
 * Class Db
 */

require_once LIB_PATH . 'telegram.php';

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

        return (int) $redis->set($key, $id);
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
            $tmp = Common::get_router();
            $bot_info = Db::get_bot_info();

            foreach ($tmp as $reg => $class) {
                //替换规则文件
                $reg = str_ireplace(array(
                    '%%bot_name%%',
                ), array(
                    $bot_info['username'],
                ), $reg);

                $router[$reg] = $class;
            }

            $redis->setex($key, 3600, json_encode($router));
        } else {
            $router = json_decode($router, true);
        }

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
