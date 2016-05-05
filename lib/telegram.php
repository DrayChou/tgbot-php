<?php
/**
 * 发消息
 * Telegram
 * @Author: dray
 * @Date:   2015-07-10 17:43:59
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-04 15:41:48
 */

class Telegram
{

    private static $instance = array();
    private $token;

    /**
     * @param null $user_id
     */
    private function __construct($token)
    {
        if (null === $token) {
            throw new Exception('error token');
        }

        $this->token = $token;
    }

    /**
     * @param null $token
     * @return Telegram
     */
    public static function singleton($token = null)
    {
        if (null === $token) {
            $token = Common::get_config('token');
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
    public function get_me()
    {
        $url = "https://api.telegram.org/bot{$this->token}/getMe";
        $res = Common::curl($url, array());

        if ($res['ok'] == true) {
            $bot_info = $res['result'];
        } else {
            return null;
        }

        if (isset($bot_info['first_name'])) {
            $bot_info['show_name'] = $bot_info['first_name'];
            if (isset($bot_info['last_name'])) {
                $bot_info['show_name'] .= ('_' . $bot_info['last_name']);
            }
        } else {
            $bot_info['show_name'] = $bot_info['username'];
        }

        return $bot_info;
    }

    /**
     * 请求最新消息
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function get_updates($data)
    {
        $url = "https://api.telegram.org/bot{$this->token}/getUpdates";
        $res = Common::curl($url, $data);

        if ($res['ok'] == true) {
            return $res['result'];
        }

        return null;
    }

    /**
     * 发送消息
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function send_message($data)
    {
        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";
        $res = Common::curl($url, $data);

        if (isset($res['ok']) && $res['ok'] == true) {
            return $res['result'];
        }

        return null;
    }
}
