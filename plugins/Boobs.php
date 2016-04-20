<?php

/**
 * 发送巨乳美图
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Boobs extends Base
{
    public static function desc()
    {
        return "/boobs - Get a boobs NSFW image. ";
    }

    public static function usage()
    {
        return array(
            "/boobs - Get a boobs NSFW image. ",
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("执行 Boobs run");

        //图片数量
        $send_image_num = 1;
        if (count($this->parms) == 2) {
            if (is_numeric($this->parms[1])) {
                $send_image_num = $this->parms[1];
            }
        }

        $url = "http://api.oboobs.ru/noise/{$send_image_num}";
        $res = Common::curl($url);

        $res_str = null;
        if (!isset($res) || !isset($res[0]['preview'])) {
            $res_str = 'Cannot get that boobs, trying another one...';
        } else {
            foreach ($res as $v) {
                $res_str = 'http://media.oboobs.ru/' . $v['preview'];
                //回复消息
                Telegram::singleton()->send_message(array(
                    'chat_id' => $this->from_id,
                    'text' => $res_str,
                    // 'reply_to_message_id' => $this->msg_id,
                ));
            }
        }

        if (empty($res_str)) {
            $res_str = 'Cannot get that boobs, trying another one...';
            //回复消息
            Telegram::singleton()->send_message(array(
                'chat_id' => $this->from_id,
                'text' => $res_str,
                // 'reply_to_message_id' => $this->msg_id,
            ));
        }
    }
}
