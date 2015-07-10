<?php

/**
 * 基础类库
 * User: dray
 * Date: 15/7/10
 * Time: 下午4:23
 */
class Base
{
    public $msg_id;
    public $chat_id;

    public $from_id;
    public $from_name;

    public $parm;

    /**
     * @param $msg
     * @throws Exception
     */
    public function set_msg($msg) {
        if (empty($msg['message_id'])) {
            throw new Exception('error message');
        }

        $this->msg_id  = $msg['message_id'];
        $this->chat_id = $msg['chat']['id'];

        $this->from_id = $msg['from']['id'];
        if (isset($msg['from']['first_name'])) {
            $this->from_name = $msg['from']['first_name'];
            if (isset($msg['from']['last_name'])) {
                $this->from_name .= ('_' . $msg['from']['last_name']);
            }
        } else {
            $this->from_name = $msg['from']['username'];
        }

        if (isset($msg['text'])) {
            $this->parm = $msg['text'];
        }
    }

    /**
     * 当有人进入群的时候
     */
    public function msg_enter_chat() {
        echo_log("有人进入群");
    }

    /**
     * 有人离开群的时候
     */
    public function msg_left_chat() {
        echo_log("有人离开群");
    }

    /**
     * 有人发照片的时候
     */
    public function msg_photo() {
        echo_log("有人发照片");
    }

    /**
     * 有人转发消息的时候
     */
    public function msg_forward() {
        echo_log("有人转发消息");
    }

    /**
     * 不管什么情况都会执行的函数
     */
    public function pre_process() {
        echo_log("每次都执行的脚本");
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        echo_log("做点什么");
    }

    /**
     * 返回命令说明，一般只有一行
     * @return string
     */
    static public function desc() {

        return "插件说明，一行，用在 help 中";
    }

    /**
     * 返回命令详细信息
     * @return string
     */
    static public function usage() {
        return "插件说明，数组，用在功能调用的说明上。";
    }
}