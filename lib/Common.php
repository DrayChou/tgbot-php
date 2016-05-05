<?php
/**
 * 通用函数库
 * Common Function Class
 * @Author: dray
 * @Date:   2015-07-10 18:43:59
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-05 13:54:34
 */

class Common
{

    private static $router = array();
    private static $config = array();

    /**
     * 打印日志
     * @param $parm
     */
    public static function echo_log($parm)
    {
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

        //检查下是不是字符串
        if (!is_string($last_message)) {
            $last_message = print_r($last_message, true);
        }

        $last_message = "\n" . date("Y-m-d H:i:s") . "\t" . Common::microtime_float() . "\t" . $last_message;
        error_log($last_message);
    }

    /**
     * 得到路由配置表
     * @return type
     */
    public static function get_router()
    {
        if (empty(self::$router)) {
            //加载基础文件
            if (!class_exists('Base')) {
                require_once PLUGIN_PATH . 'Base.php';
            }

            self::$router = array();
            if ($handle = opendir(PLUGIN_PATH)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file == "." || $file == "..") {
                        continue;
                    }

                    //插件名
                    $plugin = basename($file, ".php");

                    //跳过特殊的名称
                    if (in_array($plugin, array('Base'))) {
                        continue;
                    }

                    //跳过无效的路径
                    if (!file_exists(PLUGIN_PATH . $file)) {
                        continue;
                    }

                    // 如果没有定义路由函数，那么跳过
                    if (!class_exists($plugin)) {
                        require_once PLUGIN_PATH . $file;
                    }

                    if (!method_exists($plugin, 'router')) {
                        continue;
                    }

                    $rt = $plugin::router();
                    $rt = array_fill_keys($rt, $plugin);
                    // print_r($rt);

                    self::$router = array_merge(self::$router, $rt);
                }
                closedir($handle);
            }
        }

        return self::$router;
    }

    /**
     * 得到配置信息
     * @param type $key
     * @param type $default_value
     * @return type
     */
    public static function get_config($key = null, $default_value = null)
    {
        if (empty(self::$config)) {
            self::$config = require BASE_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php';
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
    public static function set_config($key, $value)
    {
        if (empty(self::$config)) {
            self::$config = require BASE_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php';
        }

        self::$config[$key] = $value;
    }

    /**
     * post 请求到第三方服务器，获取数据
     * @param type $url
     * @param type $data
     * @param type $res_type
     * @return type
     * @throws Exception
     */
    public static function post($url, $data, $res_type = 'json', $method = 'POST')
    {
        if (empty($url)) {
            $err = 'post error url';
            Common::echo_log($err);
            Common::report_err($err);

            return;
        }

        $before_time = self::microtime_float();

        $postdata = http_build_query($data);
        $opts = array(
            'http' => array(
                'method' => $method,
                'header' => 'Content-type: application/x-www-form-urlencoded',
            ),
        );

        //检查是否有设置代理
        $proxy = self::get_config('proxy');
        if ($proxy) {
            $opts['http']['proxy'] = $proxy;
        }

        if ($method == 'GET') {
            $url = $url . $postdata;
        } else {
            $opts['http']['content'] = $postdata;
        }

        Common::echo_log('Common: url=%s data=%s', $url, $opts);

        $context = stream_context_create($opts);
        $res = file_get_contents($url, false, $context);

        Common::echo_log('Common: time=%s res=%s', (self::microtime_float() - $before_time), $res);

        if (empty($res)) {
            $err = "post token url={$url} contents=" . print_r($opts, true) . ' res=' . print_r($res, true);
            Common::echo_log($err);
            Common::report_err($err);

            return null;
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
    public static function curl($url, $post = null, $type = 'json')
    {
        if (empty($url)) {
            $err = 'post error url';
            Common::echo_log($err);
            Common::report_err($err);

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

            //检查是否有设置代理
            $proxy = self::get_config('proxy');
            if ($proxy) {
                curl_setopt($curl, CURLOPT_PROXY, $proxy);
            }

            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $res = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);

            Common::echo_log('Common: url=%s', $url);
            Common::echo_log('Common: data=%s', print_r($post, true));
            // Common::echo_log('Common: $info=%s', print_r($info, true));

            if ($res === false || $info['http_code'] != 200) {
                $err = "post token url={$url} contents=" . print_r($post, true) . ' res=' . print_r($res, true);
                Common::echo_log($err);
                Common::report_err($err);

                return $res;
            }

            if ($type == 'json') {
                $res = json_decode($res, true);
            }

            Common::echo_log('Common: res=%s', print_r($res, true));
            Common::echo_log('Common: time=%s', (self::microtime_float() - $before_time));
        } catch (Exception $exc) {
            $err = "post token url={$url} contents=" . print_r($post, true) . ' res=' . print_r($res, true);
            Common::echo_log($err);
            Common::report_err($err);
        }

        return $res;
    }

    /**
     * 报告管理员错误
     * @param type $text
     */
    public static function report_err($text)
    {
        if (self::get_config('report')) {
            $admins = Common::get_config('admins');
            foreach ($admins as $v) {
                $msg = Telegram::singleton()->sendMessage(array(
                    'chat_id' => $v,
                    'text' => $text,
                ));

                Common::echo_log("发送信息: msg=%s", $msg);
                break;
            }
        }
    }

    /**
     * 得到时间的毫秒值
     * @return float
     */
    public static function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());

        return ((float) $usec + (float) $sec);
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
    public static function G($start, $end = '', $dec = 4)
    {
        static $_info = array();
        static $_mem = array();

        if (is_float($end)) {
            // 记录时间
            $_info[$start] = $end;
        } elseif (!empty($end)) {
            // 统计时间和内存使用
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
        } else {
            // 记录时间和内存使用
            $_info[$start] = self::microtime_float();
            $_mem[$start] = memory_get_usage();
        }
    }

    /**
     * 转换为方便识别的格式
     * @param $size
     * @return string
     */
    public static function convert_memory_size($size)
    {
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');

        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[(int) $i];
    }
}
