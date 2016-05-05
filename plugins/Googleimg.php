<?php

/**
 * 谷歌搜索图片接口 - 接口已经收费
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Googleimg extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return "/googleimg - Search image with Google API and sends it. ";
    }

    /**
     * 命令操作详解
     * Detailed command operation
     * @return array
     */
    public static function usage()
    {
        return array(
            "/googleimg info: Random search an image with Google API.",
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
            // '/googleimg',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("执行 GoogleImg run");

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $data = array(
            'v' => '1.0',
            'rsz' => 5,
            'imgsz' => 'small|medium|large',
            'q' => $this->text,
        );
        $url = "http://ajax.googleapis.com/ajax/services/search/images?" . http_build_query($data);
        $res = Common::curl($url);

        if (!isset($res['responseStatus']) || $res['responseStatus'] != 200) {
            $res_str = $res['responseDetails'];
        } else {
            $rand_key = array_rand($res['responseData']['results']);
            $rand_arr = $res['responseData']['results'][$rand_key];
            $res_str = ($rand_arr['unescapedUrl'] ? $rand_arr['unescapedUrl'] : $rand_arr['url']) . PHP_EOL;
        }

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
