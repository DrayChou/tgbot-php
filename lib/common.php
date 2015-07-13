<?php

/**
 * 通用函数库
 * Common Function Class
 * User: dray
 * Date: 15/7/10
 * Time: 下午6:43
 */
class CFun
{

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
     * 加载类库
     */
    static public function load_lib() {
        require_once(BASE_PATH . 'lib' . DIRECTORY_SEPARATOR . 'db.php');
        require_once(BASE_PATH . 'lib' . DIRECTORY_SEPARATOR . 'process.php');
        require_once(BASE_PATH . 'lib' . DIRECTORY_SEPARATOR . 'telegram.php');
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
    static public function get_config($key = NULL, $default_value = NULL) {
        if (empty(self::$config)) {
            self::$config = require(BASE_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php');
        }

        if ($key) {
            if (isset(self::$config[$key])) {
                return self::$config[$key];
            } else {
                return $default_value;
            }
        }

        return self::$config;
    }

    /**
     * post 请求到第三方服务器，获取数据
     * @param type $url
     * @param type $data
     * @param type $res_type
     * @return type
     * @throws Exception
     */
    static public function post($url, $data, $res_type = 'json', $method = 'POST') {
        if (empty($url)) {
            $err = 'post error url';
            CFun::echo_log($err);
            CFun::report_err($err);

            return;
        }

        $before_time = self::microtime_float();

        $postdata = http_build_query($data);
        $opts     = array(
            'http' => array(
                'method' => $method,
                'header' => 'Content-type: application/x-www-form-urlencoded',
            ),
        );

        //检查是否有设置代理
        $proxy = CFun::get_config('proxy');
        if ($proxy && strstr($url, 'api.telegram.org')) {
            $opts['http']['proxy'] = $proxy;
        }

        if ($method == 'GET') {
            $url = $url . $postdata;
        } else {
            $opts['http']['content'] = $postdata;
        }

        CFun::echo_log('CommonFunction: url=%s data=%s', $url, $opts);

        $context = stream_context_create($opts);
        $res     = file_get_contents($url, false, $context);

        CFun::echo_log('CommonFunction: time=%s res=%s', (self::microtime_float() - $before_time), $res);

        if (empty($res)) {
            $err = "post token url={$url} contents=" . print_r($opts, true) . ' res=' . print_r($res, true);
            CFun::echo_log($err);
            CFun::report_err($err);

            return;
        }

        if ($res_type == 'json') {
            $res = json_decode($res, true);
        }

        return $res;
    }

    /**
     * 报告管理员错误
     * @param type $text
     */
    static function report_err($text) {
//        $admins = CFun::get_config('admins');
//        foreach ($admins as $v) {
//            $msg = Telegram::singleton()->post('sendMessage', array(
//                'chat_id' => $v,
//                'text'    => $text,
//            ));
//
//            CFun::echo_log("发送信息: msg=%s", $msg);
//            break;
//        }
    }

    /**
     * 得到时间的毫秒值
     * @return float
     */
    static function microtime_float() {
        list($usec, $sec) = explode(" ", microtime());

        return ((float)$usec + (float)$sec);
    }

}
