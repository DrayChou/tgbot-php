<?php

/**
 * 管理工具
 * User: dray
 * Date: 15/7/30
 * Time: 下午3:43
 */
class Admin extends Base
{

    static function desc() {
        return "/admin - admin";
    }

    static function usage() {
        return array(
            "/admin - admin",
        );
    }

    /**
     * 不管什么情况都会执行的函数
     */
    public function pre_process() {
        //如果有调用参数，那么跳过
        if (isset($this->parms[0])) {
            return;
        }

        //如果是私聊，那么机器人接管
        if ($this->chat_id < 0) {
            return;
        }

        $this->text = $this->parm;
        $this->run();
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        CFun::echo_log("Admin run 执行");

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $is_set  = false;
        $set_arr = array('s', 'set');
        $set_flg = false;

        $bot_id = false;

        $parms = array();
        foreach ($this->parms as $k => $v) {
            if (in_array($v, self::$BOT_MAP)) {
                $bot_id = array_search($v, self::$BOT_MAP);
                continue;
            }

            if (false == $set_flg) {
                if (in_array($v, $set_arr)) {
                    $is_set  = true;
                    $set_flg = true;
                    continue;
                }
            }

            $parms[] = $v;
        }

        if ($is_set && $bot_id) {
            self::set_my_bot($this->from_id, $bot_id);

            //发送
            Telegram::singleton()->send_message(array(
                'chat_id'             => $this->chat_id,
                'text'                => '机器人已经设置好了，亲！',
                'reply_to_message_id' => $this->msg_id,
            ));
        } else {
            //发送
            Telegram::singleton()->send_message(array(
                'chat_id'             => $this->chat_id,
                'text'                => '请选择你要使用的机器人！',
                'reply_to_message_id' => $this->msg_id,
                'reply_markup'        => array(
                    'keyboard'          => array(
                        self::$BOT_MAP,
                    ),
                    'resize_keyboard'   => true,
                    'one_time_keyboard' => 'true',
                    'selective'         => 'true',
                ),
            ));
        }
    }
}