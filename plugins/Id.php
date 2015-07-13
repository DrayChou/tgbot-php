<?php

/**
 * User: dray
 * Date: 15/7/14
 * Time: 上午12:17
 */
class Id extends Base
{
    static function desc() {
        return "/id - echo my id.  ";
    }

    static function usage() {
        return array(
            "/id - echoes my id.",
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        CFun::echo_log("执行 Id run");

        $res_str = '';
        $res_str .= 'user_id:' . $this->from_id . PHP_EOL;
        $res_str .= 'chat_id:' . $this->chat_id . PHP_EOL;

        $msg = Telegram::singleton()->post('sendMessage', array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));

        CFun::echo_log("发送信息: msg=%s", $msg);
    }
}