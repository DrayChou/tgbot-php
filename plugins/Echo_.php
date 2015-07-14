<?php

/**
 * 输出点什么
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Echo_ extends Base
{
    static function desc() {
        return "/echo - echoes the msg.  ";
    }

    static function usage() {
        return array(
            "/echo [whatever] - echoes the msg.",
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        CFun::echo_log("执行 Echo_ run");

        if (empty($this->text)) {
            return;
        }

        $msg = Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => $this->text,
            'reply_to_message_id' => $this->msg_id,
        ));

        CFun::echo_log("发送信息: msg=%s", $msg);
    }
}