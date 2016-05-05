<?php

/**
 * 生成一张狗图的地址
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Dogify extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/dogify - Create a doge image with you words",
            "/狗图 - 生成一张带关键词的狗图。",
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
            "/dogify (your/words/with/slashes) - Create a doge with the image and words",
            "/狗图 (your/words/with/slashes) - 生成一张带关键词的狗图。",
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
            '/dogify',
            '/狗图',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("执行 Butts run");

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $res_str = "http://dogr.io/" . $this->text . '.png?split=true&.png';

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
