<?php
/**
 * Id
 * @Author: dray
 * @Date:   2015-07-10 15:43:59
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-04 11:20:11
 */

class Id extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/id - echoes my id.",
        );
    }

    /**
     * 命令操作详解
     * Detailed command operation
     * @return array
     */
    public static function usage()
    {
        return array(
            "/id - echoes my id.",
            "/id user - echoes user id.",
        );
    }

    /**
     * 插件的路由配置
     * plugin matching rules
     * @return array
     */
    public static function router()
    {
        //匹配的命令
        return array(
            '/id',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("执行 Id run");

        $user_id = $this->from_id;
        if (!empty($this->text)) {
            $bot = Db::get_bot_name();
            $redis = Db::get_redis();

            $chek_user = $this->text;
            $user_id = $redis->hGet($bot . 'users:usernames', $chek_user);
        }

        $res_str = '';
        $res_str .= 'user_id:' . $user_id . PHP_EOL;
        $res_str .= 'chat_id:' . $this->chat_id . PHP_EOL;

        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
