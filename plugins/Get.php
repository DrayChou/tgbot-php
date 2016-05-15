<?php

/**
 * @Author: dray
 * @Date:   2016-05-15 11:43:42
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-15 17:00:18
 */

class Get extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/Get - Get some configurations.",
            "/查看 - 查看某些配置项。",
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
            "/Get - Get some configurations.",
            "/查看 - 查看某些配置项。",
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
            '/get',
            '/查看',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("执行 Get run");

        //如果没有参数
        if (empty($this->text)) {
            return true;
        }

        $bot = Db::get_bot_name();
        $redis = Db::get_redis();

        $config_key = $bot . "chat:{$this->chat_id}:config";
        $res_str = '';

        //分隔，读取数据
        $set = strtok($this->text, ", \n\t");
        while ($set !== false) {
            $value = $redis->hGet($config_key, $set);

            $res_str .= "{$set}:{$value}" . PHP_EOL;

            $set = strtok(" \n\t");
        }

        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
