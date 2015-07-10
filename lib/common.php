<?php
/**
 * User: dray
 * Date: 15/7/10
 * Time: 下午6:43
 */

/**
 * 打印日志
 * @param $parm
 */
function vardump($parm) {
    $msg = func_get_args();
    if (1 === count($msg)) {
        // 可変長引数がひとつであったとき
        $last_message = $msg[0];
    } else {
        $format       = array_shift($msg); // vsprintfのformat(=$format)とargs(=$msg)を分離する
        $last_message = vsprintf($format, $msg);
    }

    echo $last_message . PHP_EOL;
}

/**
 * 读取 update_id
 * @return int
 * @throws Exception
 */
function get_update_id() {
    global $config;
    if (empty($config) || empty($config['bot_name'])) {
        throw new Exception('error bot_name');
    }

    $bot = $config['bot_name'];
    $key = $bot . ':' . 'update_id';

    $redis = new Redis();

    return (int)$redis->get($key);
}

/**
 * 设置 update_id
 * @param $id
 * @return int
 * @throws Exception
 */
function set_update_id($id) {
    global $config;
    if (empty($config) || empty($config['bot_name'])) {
        throw new Exception('error bot_name');
    }

    $bot = $config['bot_name'];
    $key = $bot . ':' . 'update_id';

    $redis = new Redis();

    return (int)$redis->set($key, $id);
}
