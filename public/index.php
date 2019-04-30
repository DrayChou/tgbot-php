<?php
/**
 * @Author: dray
 * @Date:   2015-07-10 11:53:59
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-03 14:45:35
 */

//加载包文件
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'init.php';

//如果有 token 带过来，那么调用对应的机器人
if (isset($_GET['token'])) {

    // 设置 token
    Common::set_config('token', $_GET['token']);

    //设置日志
    ini_set("display_errors", 0);
    if ($log_path = Common::get_config('log_path')) {
        ini_set("error_reporting", E_ALL);
        ini_set("error_log", $log_path . Db::get_bot_name('-') . '.log');
        ini_set("log_errors", 1);
    }

    //如果需要设置回调
    if (isset($_GET['setWebhook'])) {
        $res = Common::post(
            "https://api.telegram.org/bot{$_GET['token']}/setWebhook",
            array(
                'url' => "https://{$_SERVER['SERVER_NAME']}/?token={$_GET['token']}",
            )
        );
        if ($res) {
            echo 'setWebhook success!' . PHP_EOL;
        } else {
            echo 'setWebhook failed!' . PHP_EOL;
        }

        exit();
    }
    
    //添加转发接口
    if (isset($_GET['api'])) {
        $api = $_GET['api']; 
        $get_data = $_GET;
        $post_data = file_get_contents('php://input');
        if (is_string($post_data)) {
            $post_data = @json_decode($post_data);
        }

        unset($get_data["token"]); 
        unset($get_data["api"]); 

        $data = array();
        $url = "https://api.telegram.org/bot{$_GET['token']}/{$api}";
        if (!empty($get_data)) {
            $url .= ("?" . http_build_query($get_data));
        }

        if (empty($post_data)) {
            $method = "GET";
        } else {
            $method = "POST";
            $data = $post_data;
        }

        $res = Common::post($url, $data, "json", $method);

        header('Content-type: application/json');
        exit(json_encode($res));
    }

    Common::G('run_start');

    //接收数据，并处理
    $input = file_get_contents('php://input');
    Process::run(array($input));

    Common::G('run_end');
    $log = '耗时：' . Common::G('run_start', 'run_end') . ' 当前占内存：' . Common::convert_memory_size(memory_get_usage()) . PHP_EOL;
    Common::echo_log($log);
} else {
    echo 'This is ' . BOT . '!' . PHP_EOL;
}
