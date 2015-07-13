<?php

/**
 * 发消息
 * User: dray
 * Date: 15/7/10
 * Time: 下午5:22
 */
class Telegram
{

    static private $instance = array();
    private $token;

    /**
     * @param null $user_id
     */
    private function __construct($token) {
        if (NULL === $token) {
            throw new Exception('error token');
        }

        $this->token = $token;
    }

    /**
     * @param null $token
     * @return Telegram
     */
    static public function singleton($token = NULL) {
        if (NULL === $token) {
            $token = CFun::get_config('token');
            if (empty($token)) {
                throw new Exception('error token');
            }
        }

        if (!isset(self::$instance[$token])) {
            self::$instance[$token] = new self($token);
        }

        return self::$instance[$token];
    }

    /**
     * 得到机器人的信息
     * @return array
     */
    public function get_me() {
        $token = CFun::get_config('token');

        $redis    = Db::get_redis();
        $bot_info = $redis->get($token);
        if (empty($bot_info)) {
            $bot_info = $this->post('getMe', array());

            $redis->set($token, json_encode($bot_info));
        } else {
            $bot_info = json_decode($bot_info, true);
        }

        return $bot_info;
    }

    /**
     * 发送请求出去
     * @param $comm
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function post($comm, $data) {
        $url = "https://api.telegram.org/bot{$this->token}/{$comm}";
        $res = CFun::post($url, $data);

        if ($res['ok'] == true) {
            return $res['result'];
        }

        return NULL;
    }

}
