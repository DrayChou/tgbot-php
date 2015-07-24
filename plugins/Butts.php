<?php

/**
 * 发送巨臀美图
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Butts extends Base
{
    static function desc() {
        return "/butts - Get a butts NSFW image. ";
    }

    static function usage() {
        return array(
            "/butts - Get a butts NSFW image. ",
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        CFun::echo_log("执行 Butts run");

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $url = "http://api.obutts.ru/noise/1";
        $res = CFun::curl($url);

        if (!isset($res) || !isset($res[0]['preview'])) {
            $res_str = 'Cannot get that butts, trying another one...';
        } else {
            $res_str = 'http://media.obutts.ru/' . $res[0]['preview'];
        }

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
