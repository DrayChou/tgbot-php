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

        //echo $last_message . PHP_EOL;
        if(!is_string($last_message)){
            $last_message = print_r($last_message, true);
        }
        error_log($last_message, 0);
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
     * @param type $key
     * @param type $default_value
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
     * 设置配置信息
     * @param type $key
     * @param type $value
     * @return type
     */
    static public function set_config($key, $value){
        self::$config[$key] = $$value;
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
        $proxy = self::get_config('proxy');
        if (
            $proxy &&
            (
                strstr($url, 'api.telegram.org') ||
                strstr($url, 'google')
            )
        ) {
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

            return NULL;
        }

        if ($res_type == 'json') {
            $res = json_decode($res, true);
        }

        return $res;
    }

    /**
     * 请求到第三方服务器，获取数据 如果 Post 有数据，那就是 post 请求
     * @param string $url
     * @param array $post
     */
    static function curl($url, $post = NULL) {
        if (empty($url)) {
            $err = 'post error url';
            CFun::echo_log($err);
            CFun::report_err($err);

            return;
        }

        $before_time = self::microtime_float();

        try {

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            if (!is_null($post)) {
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
            }

            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $res  = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);

            CFun::echo_log('CommonFunction: time=%s res=%s', (self::microtime_float() - $before_time), $res);

            if ($res === false || $info['http_code'] != 200) {
                $err = "post token url={$url} contents=" . print_r($post, true) . ' res=' . print_r($res, true) . ' info=' . print_r($info, true);
                CFun::echo_log($err);
                CFun::report_err($err);

                return NULL;
            }

        } catch (Exception $exc) {
            $err = "post token url={$url} contents=" . print_r($post, true) . ' res=' . print_r($res, true);
            CFun::echo_log($err);
            CFun::report_err($err);
        }

        $res = json_decode($res, true);

        return $res;
    }

    /**
     * 报告管理员错误
     * @param type $text
     */
    static function report_err($text) {
//        $admins = CFun::get_config('admins');
//        foreach ($admins as $v) {
//            $msg = Telegram::singleton()->sendMessage(array(
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

    /**
     * 记录和统计时间（微秒）和内存使用情况
     * 使用方法:
     * <code>
     * G('begin'); // 记录开始标记位
     * // ... 区间运行代码
     * G('end'); // 记录结束标签位
     * echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
     * echo G('begin','end','m'); // 统计区间内存使用情况
     * 如果end标记位没有定义，则会自动以当前作为标记位
     * </code>
     * @param string $start 开始标签
     * @param string $end 结束标签
     * @param integer|string $dec 小数位或者m
     * @return mixed
     */
    static function G($start, $end = '', $dec = 4) {
        static $_info = array();
        static $_mem = array();

        if (is_float($end)) { // 记录时间
            $_info[$start] = $end;
        } elseif (!empty($end)) { // 统计时间和内存使用
            if (!isset($_info[$end])) {
                $_info[$end] = self::microtime_float();
            }

            if ($dec == 'm') {
                if (!isset($_mem[$end])) {
                    $_mem[$end] = memory_get_usage();
                }

                return number_format(($_mem[$end] - $_mem[$start]) / 1024);
            } else {
                return number_format(($_info[$end] - $_info[$start]), $dec);
            }
        } else { // 记录时间和内存使用
            $_info[$start] = self::microtime_float();
            $_mem[$start]  = memory_get_usage();
        }
    }

    /**
     * 转换为方便识别的格式
     * @param $size
     * @return string
     */
    static function convert_memory_size($size) {
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');

        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[(int)$i];
    }
}
