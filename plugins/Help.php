<?php
/**
 * 帮助
 * Help
 * @Author: dray
 * @Date:   2015-07-10 15:43:59
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-12 16:42:54
 */

class Help extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/help - Help plugin. Get info from other plugins.",
            "/帮助 - 返回帮助信息。",
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
            "/help - Show list of plugins.",
            "/help all - Show all commands for every plugin.",
            "/help [plugin name] -  Commands for that plugin.",
            "/帮助 - 显示插件列表。",
            "/帮助 所有 - 显示所有插件的命令详情。.",
            "/帮助 [plugin name] -  单个插件的命令详情。",
        );
    }

    /**
     * 插件的路由配置
     * plugin matching rules
     * @return array
     */
    public static function router()
    {
        return array(
            //帮助脚本匹配的命令
            '/help',
            '/帮助',
        );
    }

    /**
     * 得到说明信息
     * 如果有参数，那么就拿一个的，否则拿取所有的
     * @param null $text
     * @return string
     */
    private function get_helps($text = null)
    {
        $helps = array();
        $router = Db::get_router();
        $bot_info = Db::get_bot_info();

        //抓文字里的关键词，抓到是要请求什么插件
        $one = false;
        foreach ($router as $reg => $class) {
            if ($text) {
                $desc = null;

                // 如果是单个拿取的话，直接跳出
                if (strtolower($class) == strtolower($text)) {
                    $desc = $class::usage();
                } elseif ($reg == ('/' . $text)) {
                    $desc = $class::usage();
                }

                if (!empty($desc)) {
                    if (!is_array($desc)) {
                        $desc = array($desc);
                    }

                    $helps = array_merge($helps, $desc);
                    $one = true;
                    break;
                }
            }

            //如果是拿取所有的信息的话
            if (strtolower($text) == 'all') {
                $desc = $class::usage();
            } else {
                $desc = $class::desc();
            }

            if (!is_array($desc)) {
                $desc = array($desc);
            }

            $helps = array_merge($helps, $desc);
        }

        if (false == $one) {
            $tmp = array_unique($helps);
            $helps = array_merge(
                array(
                    'Welcome to use ' . $bot_info['show_name'],
                    '',
                ),
                $tmp,
                array(
                    '',
                    'GitHub: https://github.com/DrayChou/tgbot-php',
                    'Author: @drayc',
                )
            );
        }

        return implode(PHP_EOL, $helps);
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("执行 Help run");

        $res_str = $this->get_helps($this->text);
        //帮助信息太长的话，就私信给个人
        if (empty($this->text) || strtolower($this->text) == 'all') {
            //发送给个人
            $msg = Telegram::singleton()->send_message(array(
                'chat_id' => $this->from_id,
                'text' => $res_str,
            ));
        }

        //发送到群组里
        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => 'I send you a message about it.',
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
