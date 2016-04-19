<?php

/**
 * 发送巨乳美图
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Boobs extends Base
{
    static function desc() {
        return "/boobs - Get a boobs NSFW image. ";
    }

    static function usage() {
        return array(
            "/boobs - Get a boobs NSFW image. ",
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        Common::echo_log("执行 Boobs run");

        $url = "http://api.oboobs.ru/noise/1";
        $res = Common::curl($url);

        if (!isset($res) || !isset($res[0]['preview'])) {
            $res_str = 'Cannot get that boobs, trying another one...';
        } else {
            $res_str = 'http://media.oboobs.ru/' . $res[0]['preview'];
        }

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
