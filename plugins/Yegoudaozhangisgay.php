<?php

/**
 * @Author: dray
 * @Date:   2016-04-22 11:01:45
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-05 13:45:39
 */

include_once 'Bot.php';

class Yegoudaozhangisgay extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/yegoudaozhangisgay - 野狗道长是个 gay ?  ",
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
            "/yegoudaozhangisgay - 野狗道长是个 gay ?",
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
            '/yegoudaozhangisgay',
            '@yegoudaozhangisgay',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("执行 Yegoudaozhangisgay run");

        $text = '野狗道长是个同性恋？';
        $bot = Bot::get_my_bot($this->from_id, $text);

        //调用机器人
        $bot->chat_id = $this->chat_id;
        $bot->msg_id = $this->msg_id;
        $bot->text = $text;
        $bot->run();
    }
}
