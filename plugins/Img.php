<?php

/**
 * 搜索图片接口
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43.
 */
class Img extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        array(
            '/img - Random search an image.',
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
            '/img info - Random search an image.',
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
            '/img',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数.
     */
    public function run()
    {
        Common::echo_log('执行 Img run');

        $help = Process::get_class('Pixabay');

        $help->text = $this->text;
        $help->chat_id = $this->chat_id;
        $help->from_id = $this->from_id;
        $help->msg_id = $this->msg_id;
        $help->run();
    }
}
