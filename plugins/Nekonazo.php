<?php

/**
 * 萌娘回路
 * http://wiki.oekaki.so/%E8%90%8C%E5%A8%98%E5%9B%9E%E8%B7%AF_%EF%BD%9E_Moec_Core_%EF%BC%88%E4%BD%9C%E5%93%81%EF%BC%89
 * User: dray
 * Date: 15/7/27
 * Time: 下午7:01
 */
class Nekonazo extends Base
{
    public static function desc()
    {
        return "/neko - Get a boobs NSFW image. ";
    }

    public static function usage()
    {
        return array(
            "/neko - Get a boobs NSFW image. ",
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("执行 Boobs run");

        $url = "http://api.oboobs.ru/noise/1";
        $res = Common::curl($url);

        if (!isset($res) || !isset($res[0]['preview'])) {
            $res_str = 'Cannot get that boobs, trying another one...';
        } else {
            $res_str = 'http://media.oboobs.ru/' . $res[0]['preview'];
        }

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
