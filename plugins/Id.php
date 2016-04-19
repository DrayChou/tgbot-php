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
            "/id user - echoes user id.",
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        Common::echo_log("执行 Id run");

        $user_id = $this->from_id;
        if (!empty($this->text)) {
            $bot   = Db::get_bot_name();
            $redis = Db::get_redis();

            $chek_user = $this->text;
            $user_id   = $redis->hGet($bot . 'users:usernames', $chek_user);
        }

        $res_str = '';
        $res_str .= 'user_id:' . $user_id . PHP_EOL;
        $res_str .= 'chat_id:' . $this->chat_id . PHP_EOL;

        Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}