<?php

/**
 * 谷歌搜索图片接口
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Img extends Base
{
    static function desc() {
        return "/img - Search image with Google API and sends it. ";
    }

    static function usage() {
        return array(
            "/img info: Random search an image with Google API.",
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        CFun::echo_log("执行 Google run");

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $data = array(
            'v'     => '1.0',
            'rsz'   => 5,
            'imgsz' => 'small|medium|large',
            'q'     => $this->text,
        );
        $url  = "http://ajax.googleapis.com/ajax/services/search/images?" . http_build_query($data);
        $res  = CFun::curl($url);
        CFun::echo_log("发送 Img 查询: res=%s", $res);

        $res_str = '';
        if (!isset($res['responseStatus']) || $res['responseStatus'] != 200) {
            $res_str = $res['responseDetails'];
        } else {
            $rand_key = array_rand($res['responseData']['results']);
            $rand_arr = $res['responseData']['results'][$rand_key];
            $res_str  = ($rand_arr['unescapedUrl'] ? $rand_arr['unescapedUrl'] : $rand_arr['url']) . PHP_EOL;
        }

        //回复消息
        $msg = Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));

        CFun::echo_log("发送信息: msg=%s", $msg);
    }

}
