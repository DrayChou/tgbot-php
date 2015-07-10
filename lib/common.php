<?php

/**
 * User: dray
 * Date: 15/7/10
 * Time: 下午6:43
 */
class CommonFunction {

    static private $router = array();
    static private $config = array();

    /**
     * 打印日志
     * @param $parm
     */
    static function echo_log($parm) {
        $msg = func_get_args();
        if (1 === count($msg)) {
            // 可変長引数がひとつであったとき
            $last_message = $msg[0];
        } else {
            $format = array_shift($msg); // vsprintfのformat(=$format)とargs(=$msg)を分離する

            foreach ($msg as $k => $v) {
                if (!is_string($v)) {
                    $msg[$k] = print_r($v, true);
                }
            }

            $last_message = vsprintf($format, $msg);
        }

        echo $last_message . PHP_EOL;
    }

    /**
     * 得到路由配置表
     * @return type
     */
    static public function get_router() {
        if (empty(self::$router)) {
            self::$router = require(BASE_PATH . 'config' . DIRECTORY_SEPARATOR . 'router.php');
        }
        return self::$router;
    }

    /**
     * 得到配置信息
     * @return type
     */
    static public function get_config() {
        if (empty(self::$config)) {
            self::$config = require(BASE_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php');
        }
        return self::$config;
    }
}
