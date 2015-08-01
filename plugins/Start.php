<?php

/**
 * 开始玩
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Start extends Base {

    static function desc() {
        return "/start - Start using robots.";
    }

    static function usage() {
        return array(
            "/start - Start using robots.",
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        CFun::echo_log("执行 Start run");

        Process::get_class('Help')->run();
    }

}
