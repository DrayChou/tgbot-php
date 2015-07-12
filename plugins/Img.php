<?php

/**
 * 谷歌搜索图片接口
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Tuling extends Base {

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
        CommonFunction::echo_log("执行 Google run");

        $url  = "http://ajax.googleapis.com/ajax/services/search/images?";
        $data = array(
            'v'     => '1.0',
            'rsz'   => 5,
            'imgsz' => 'small|medium|large',
            'q'     => $this->text,
        );

        $res = CommonFunction::post($url, $data, 'json', 'GET');
        CommonFunction::echo_log("发送 bot 查询: res=%s", $res);

        if (!isset($res['responseStatus']) || $res['responseStatus'] != 200) {
            throw new Exception('google error code:' . $res['responseStatus']);
        }

        $res_str = '';
        foreach ($res['responseData']['results'] as $v) {
            $res_str = $res_str . $v['titleNoFormatting'] . ' - ' . ($v['unescapedUrl'] ? $v['unescapedUrl'] : $v['url']) . PHP_EOL;
        }

        //回复消息
        $msg = Telegram::singleton()->post('sendMessage', array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));

        CommonFunction::echo_log("发送信息: msg=%s", $msg);
    }

}
