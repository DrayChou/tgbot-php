<?php

/**
 * 输出点什么
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Echo_ extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/echo - echoes the msg.",
            "/说 - 说点啥.",
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
            "/echo [whatever] - echoes the msg.",
            "/说 [啥]- 说点啥.",
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
            '/echo',
            '/说',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("执行 Echo_ run");

        if (empty($this->text)) {
            return;
        }

        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $this->text,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
