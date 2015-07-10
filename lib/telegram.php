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
            global $config;
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
        $url      = "https://api.telegram.org/bot{$this->token}/{$comm}";
        $postdata = http_build_query($data);

        $opts    = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );
        $context = stream_context_create($opts);
        $res     = file_get_contents($url, false, $context);

        if (empty($res)) {
            throw new Exception("post token url={$url} contents=" . print_r($opts, true));
        }

        $res_arr = json_decode($res, true);
        if ($res_arr['ok'] == true) {
            return $res_arr['result'];
        }

        return NULL;
    }
}