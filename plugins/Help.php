<?php

/**
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Help extends Base
{
    static function desc() {

        return "/help - Help plugin. Get info from other plugins.  ";
    }

    static function usage() {
        return array(
            "/help: Show list of plugins.",
            "/help all: Show all commands for every plugin.",
            "/help [plugin name]: Commands for that plugin."
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        CommonFunction::echo_log("执行 Help run");

        $helps = Process::get_helps();

        $msg = Telegram::singleton()->post('sendMessage', array(
            'chat_id'             => $this->chat_id,
            'text'                => $helps,
            'reply_to_message_id' => $this->msg_id,
        ));

        CommonFunction::echo_log("发送信息: msg=%s", $msg);
    }
}