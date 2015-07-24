<?php

/**
 * Calculate math expressions with mathjs API
 * http://api.mathjs.org/v1/
 * User: dray
 * Date: 15/7/24
 * Time: 下午6:05
 */
class Calculate extends Base
{
    static function desc() {
        return "/calc [expression]: Calculate math expressions with mathjs API.";
    }

    static function usage() {
        return array(
            "/calc [expression]: evaluates the expression and sends the result.",
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        CFun::echo_log("执行 Calculate run");

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $data = array(
            'expr' => $this->text,
        );
        $url  = "http://api.mathjs.org/v1/?" . http_build_query($data);
        $res  = CFun::curl($url);

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => $res,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
