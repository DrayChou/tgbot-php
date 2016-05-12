<?php

/**
 * User: dray
 * Date: 15/7/30
 * Time: 下午3:43
 */
class Bot extends Base
{

    //目前备选的机器人
    const BOT_TULING123 = 1;
    const BOT_CLEVER = 2;

    public static $BOT_MAP = array(
        self::BOT_TULING123 => 'tuling123',
        self::BOT_CLEVER => 'cleverbot',
    );

    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/bot - Dialogue with bot",
            "/机器人 - 跟机器人说话...",
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
            "/bot set - set default bot.",
            "/bot - Dialogue with bot",
            "/机器人 set - 设置默认机器人",
            "/机器人 - 跟机器人说话",
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
            '/bot',
            '/机器人',
            '/智能',
            '/罗伯特',
        );
    }

    /**
     * 得到当前设置的机器人
     * @param $user_id
     * @return Base|null
     */
    public static function get_my_bot($user_id, $param = null)
    {
        $bot_id = (int) Db::get_redis()->hGet('bot_index', $user_id);

        //如果没有设置过机器人，那么使用默认机器人
        if (!isset(self::$BOT_MAP[$bot_id])) {
            $bot_id = 1;

            if (!empty($param)) {
                if (preg_match("/^[\x7f-\xff]+$/", $param)) {
                    //兼容gb2312,utf-8
                    $bot_id = 1;
                } else {
                    $bot_id = 2;
                }
            }
        }

        return Process::get_class(self::$BOT_MAP[$bot_id]);
    }

    /**
     * 设置用户的机器人
     * @param $user_id
     * @param $bot_id
     * @return int|null
     */
    public static function set_my_bot($user_id, $bot_id)
    {
        if (!isset(self::$BOT_MAP[$bot_id])) {
            return null;
        }

        return Db::get_redis()->hSet('bot_index', $user_id, $bot_id);
    }

    /**
     * 不管什么情况都会执行的函数
     */
    public function pre_process()
    {
        //如果是私聊，那么机器人接管
        if ($this->chat_id > 0) {
            //如果有调用参数，那么跳过
            if (!empty($this->common)) {
                Common::echo_log("Bot pre_process 已经有命令要执行 跳过 common=%s", $this->common);
                return;
            }

            //如果之前有命令调用
            if ($this->is_has_reply()) {
                Common::echo_log("Bot pre_process 有命令需要回复 跳过 common=%s", $this->common);
                return;
            }

            $bot = self::get_my_bot($this->from_id, $this->parm);
            if ($bot) {
                $bot->set_msg($this->msg, $this->msg['text']);
                $bot->run();
            }
        }
    }

    /**
     * 有人回复我
     */
    public function msg_reply_me()
    {
        //群组聊天的时候，开启这个模式，方式跟私聊的冲突
        if ($this->chat_id < 0) {
            //如果有调用参数，那么跳过
            if (!empty($this->common)) {
                Common::echo_log("Bot pre_process 已经有命令要执行 跳过 common=%s", $this->common);
                return;
            }

            $bot = self::get_my_bot($this->from_id, $this->parm);
            if ($bot) {
                $bot->set_msg($this->msg, $this->text);
                $bot->run();
            }
        }
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("Bot run 执行");

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $is_set = false;
        $set_arr = array('s', 'set');
        $set_flg = false;

        $bot_id = false;

        $parms = array();
        foreach ($this->parms as $k => $v) {
            if ($bot_id = array_search(strtolower($v), self::$BOT_MAP)) {
                continue;
            }

            if (false == $set_flg) {
                if (in_array($v, $set_arr)) {
                    $is_set = true;
                    $set_flg = true;
                    continue;
                }
            }

            $parms[] = $v;
        }

        $bot = self::get_my_bot($this->from_id, $this->parm);
        if (empty($bot) || $is_set) {
            if (empty($bot_id)) {
                $key_board = null;
                foreach (self::$BOT_MAP as $v) {
                    $key_board[] = array(
                        '/bot set ' . $v,
                    );
                }

                //发送
                Telegram::singleton()->send_message(array(
                    'chat_id' => $this->chat_id,
                    'text' => '请选择你要使用的机器人！' . PHP_EOL . '目前支持：' . PHP_EOL . implode(PHP_EOL, self::$BOT_MAP) . PHP_EOL,
                    'reply_to_message_id' => $this->msg_id,
                    'reply_markup' => json_encode(array(
                        'keyboard' => $key_board,
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                        'selective' => true,
                    )),
                ));
            } else {
                //发送
                self::set_my_bot($this->from_id, $bot_id);
                Telegram::singleton()->send_message(array(
                    'chat_id' => $this->chat_id,
                    'text' => '机器人已经设置好了，亲！',
                    'reply_to_message_id' => $this->msg_id,
                ));
            }

            return;
        }

        //调用机器人
        $bot->set_msg($this->msg, $this->text);
        $bot->run();
    }
}
