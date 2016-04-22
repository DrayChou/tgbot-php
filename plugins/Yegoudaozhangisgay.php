<?php

/**
 * @Author: dray
 * @Date:   2016-04-22 11:01:45
 * @Last Modified by:   dray
 * @Last Modified time: 2016-04-22 11:18:48
 */

include_once 'Bot.php';

class Yegoudaozhangisgay extends Base
{
    public static function desc()
    {
        return "/yegoudaozhangisgay - 野狗道长是个 gay ?  ";
    }

    public static function usage()
    {
        return array(
            "/yegoudaozhangisgay - 野狗道长是个 gay ?",
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
        $bot->text = $text;
        $bot->run();
    }
}
