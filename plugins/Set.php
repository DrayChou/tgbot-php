<?php

/**
 * @Author: dray
 * @Date:   2016-05-15 11:43:42
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-15 16:59:54
 */

class Set extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/Set - Setting some configuration item.",
            "/设定 - 设定配置值。",
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
            "/Set - Setting some configuration item.",
            "/设定 - 设定配置值。",
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
            '/set',
            '/设定',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("执行 Set run");

        //如果没有参数
        if (empty($this->text)) {
            return true;
        }

        //检查参数
        $tmp = explode(' ', $this->text, 2);
        if (count($tmp) != 2) {
            return true;
        }

        $bot = Db::get_bot_name();
        $redis = Db::get_redis();

        $config_key = $bot . "chat:{$this->chat_id}:config";
        $admins_key = $bot . "chat:{$this->chat_id}:admins";

        //检查权限
        $admins = Common::get_config('admins');
        $chat_admins = $redis->hGetAll($admins_key);
        $admins = array_merge($admins, array_keys($chat_admins));
        if (!in_array($this->from_id, $admins)) {
            return;
        }

        $set = $tmp[0];
        $value = $tmp[1];
        $res_str = 'Setting failed!';

        //设置值
        if (!(false === $redis->hSet($config_key, $set, $value))) {
            $res_str = 'Setting success!';
        }

        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
