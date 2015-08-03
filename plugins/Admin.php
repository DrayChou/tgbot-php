<?php

/**
 * 管理工具
 * User: dray
 * Date: 15/7/30
 * Time: 下午3:43
 */
class Admin extends Base
{
    static $ADMIN_MAP = array(
        'ResetRouting'
    );

    static function desc() {
        return "/admin - admin";
    }

    static function usage() {
        return array(
            "/admin - admin",
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        CFun::echo_log("Admin run 执行");

        $do_   = false;
        $parms = array();
        foreach ($this->parms as $k => $v) {
            if ($do_ = array_search(strtolower($v), self::$ADMIN_MAP)) {
                continue;
            }

            $parms[] = $v;
        }

        if (empty($do_)) {
            $key_board = NULL;
            foreach (self::$ADMIN_MAP as $v) {
                $key_board[] = array(
                    '/admin ' . $v,
                );
            }

            //发送
            Telegram::singleton()->send_message(array(
                'chat_id'             => $this->chat_id,
                'text'                => '请选择你要使用的功能！' . PHP_EOL . '目前支持：' . PHP_EOL . implode(PHP_EOL, self::$ADMIN_MAP) . PHP_EOL,
                'reply_to_message_id' => $this->msg_id,
                'reply_markup'        => json_encode(array(
                    'keyboard'          => $key_board,
                    'resize_keyboard'   => true,
                    'one_time_keyboard' => true,
                    'selective'         => true,
                )),
            ));

            return;
        }

        switch ($do_) {
            case self::$ADMIN_MAP[0]: {
                Db::get_router(true);
                break;
            }
        }

        //发送
        Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => '操作完成，亲！',
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}