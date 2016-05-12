<?php

/**
 * 开始玩
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Start extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/start - Start using robots.",
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
            "/start - Start using robots.",
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
            '/start',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("执行 Start run");

        $help = Process::get_class('Help');

        $help->set_msg($this->msg, $this->text);
        $help->run();
    }
}
