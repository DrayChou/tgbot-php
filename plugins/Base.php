<?php

/**
 * 基础类库
 * User: dray
 * Date: 15/7/10
 * Time: 下午4:23
 */
class Base
{
    public $parm;
    public $chat_id;
    public $msg_id;

    public function __construct($msg) {
        if (empty($msg['message_id'])) {
            throw new Exception('error message');
        }

        $this->msg_id = $msg['message_id'];

        $this->chat_id = $msg['chat']['id'];

        if (isset($msg['text'])) {
            $this->parm = $msg['text'];
        }
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

    /**
     * 当有人进入群的时候
     */
    static public function msg_enter_chat() {
        echo '有人进入群';
    }

    /**
     * 有人离开群的时候
     */
    static public function msg_left_chat() {
        echo '有人离开群';
    }

    /**
     * 有人发照片的时候
     */
    static public function msg_photo() {
        echo '有人发照片';
    }

    /**
     * 有人转发消息的时候
     */
    static public function msg_forward() {
        echo '有人转发消息';
    }

    /**
     * 不管什么情况都会执行的函数
     */
    static public function pre_process() {
        echo '每次都执行的脚本';
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        echo '做点什么！';
    }
}