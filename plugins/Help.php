<?php

/**
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Help extends Base
{
    static function desc() {
        return "/help - Help plugin. Get info from other plugins.  ";
    }

    static function usage() {
        return array(
            "/help - Show list of plugins.",
            "/help all - Show all commands for every plugin.",
            "/help [plugin name] -  Commands for that plugin.",
        );
    }

    /**
     * 得到说明信息
     * 如果有参数，那么就拿一个的，否则拿取所有的
     * @param null $text
     * @return string
     */
    private function get_helps($text = NULL) {
        $helps    = array();
        $router   = Db::get_router();
        $plugins  = array_flip($router);
        $bot_info = Telegram::singleton()->get_me();

        $one = false;
        foreach ($plugins as $class_name => $tmp) {
            $class = Process::get_class($class_name);

            // 如果是单个拿取的话，直接跳出
            if ($text && strtolower($class_name) == strtolower($text)) {
                $desc = $class::usage();
                if (!is_array($desc)) {
                    $desc = array($desc);
                }

                $helps = $desc;
                $one   = true;
                break;
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
            $helps = array_merge(
                array(
                    'Welcome to use ' . $bot_info['show_name'],
                    '',
                ),
                $helps,
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
    public function run() {
        CFun::echo_log("执行 Help run");

        $res_str = $this->get_helps($this->text);
        if (empty($this->text) || strtolower($this->text) == 'all') {
            //发送给个人
            $msg = Telegram::singleton()->send_message(array(
                'chat_id' => $this->from_id,
                'text'    => $res_str,
            ));
            CFun::echo_log("发送信息: msg=%s", $msg);

            //帮助信息太长的话，就私信给个人
            $res_str = 'I send you a message about it.';
        }

        //发送到群组里
        $msg = Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
        CFun::echo_log("发送信息: msg=%s", $msg);
    }
}