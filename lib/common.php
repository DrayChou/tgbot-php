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
    static public function get_config($key = NULL) {
        if (empty(self::$config)) {
            self::$config = require(BASE_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php');
        }

        if (isset(self::$config[$key])) {
            return self::$config[$key];
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
            throw new Exception('post error url');
        }

        $postdata = http_build_query($data);
        $opts     = array(
            'http' => array(
                'method' => $method,
                'header' => 'Content-type: application/x-www-form-urlencoded',
            )
        );

        if ($method == 'GET') {
            $url = $url . $postdata;
        } else {
            $opts['http']['content'] = $postdata;
        }

        CommonFunction::echo_log('CommonFunction: url=%s data=%s', $url, $opts);

        $context = stream_context_create($opts);
        $res     = file_get_contents($url, false, $context);

        CommonFunction::echo_log('CommonFunction: res=%s', $res);

        if (empty($res)) {
            throw new Exception("post token url={$url} contents=" . print_r($opts, true));
        }

        if ($res_type == 'json') {
            $res = json_decode($res, TRUE);
        }

        return $res;
    }

}
