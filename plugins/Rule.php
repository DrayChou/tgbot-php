<?php

/**
 * @Author: dray
 * @Date:   2016-05-15 11:43:42
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-15 17:13:24
 */

class Rule extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/rule - Print out the rules.",
            "/规则 - 打印出群的规则",
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
            "/rule - Print out the rules.",
            "/规则 - 打印出群的规则",
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
            '/rule',
            '/规则',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("执行 Rule run");

        // 如果是 私聊
        if ($this->chat_id > 0) {
            return true;
        }

        $bot = Db::get_bot_name();
        $redis = Db::get_redis();
        $key = $bot . "chat:{$this->chat_id}:config";
        $res_str = '';

        //拿取之前设定的规则
        $rules = $redis->hGet($key, 'rules');

        //设置默认规则
        if (empty($rules)) {
            $res_str = "【成员】：\n★:不得在工作时间发送色情内(07:00-18:00)。\n★:不得发表极端政治言论。\n★:请勿在群组内撕逼。\n★:不得通过更换头像和昵称伪装他人。\n\n【管理员】:\n★:避免过多的置顶消息，且附带全体通知的置顶消息每天总量不超过2条，避免在休息时间发送。\n";

            $yegou_chat_id = -1001002187939;
            if ($this->chat_id == $yegou_chat_id) {
                $res_str += "\n群链接：\nhttps://telegram.me/joinchat/BQCpQju8LKOx3A-wlDckkw";
            }

            $redis->hSet($key, 'rules', $res_str);
        } else {
            $res_str = $rules;
        }

        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
