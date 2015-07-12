<?php

/**
 * 发消息
 * User: dray
 * Date: 15/7/10
 * Time: 下午5:22
 */
class Telegram {

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
            $config = CommonFunction::get_config();
            if (empty($config) || empty($config['token'])) {
                throw new Exception('error token');
            }

            $token = $config['token'];
        }

        if (!isset(self::$instance[$token])) {
            self::$instance[$token] = new self($token);
        }

        return self::$instance[$token];
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
        $res = CommonFunction::post($url, $data);

        if ($res['ok'] == true) {
            return $res['result'];
        }

        return NULL;
    }

}
