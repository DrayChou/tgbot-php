<?php

/**
 * 知乎日报
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Zhihu extends Base
{
    public static function desc()
    {
        return "/zhihu - 知乎日报";
    }

    public static function usage()
    {
        return array(
            "/zhihu - 知乎日报"
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     * @throws Exception
     */
    public function run()
    {
        Common::echo_log("Zhihu run 执行");

        $url = "http://news-at.zhihu.com/api/4/news/latest";
        $res = Common::curl($url);

        if (!isset($res['stories'])) {
            $res_str = '好像出问题了，稍后再试下吧！';
        } else {
            $rand_key = array_rand($res['stories']);
            $tmp      = $res['stories'][$rand_key];
            $res_str  = $tmp['title'] . PHP_EOL;
            $res_str .= ('http://daily.zhihu.com/story/' . $tmp['id']) . PHP_EOL;
            $res_str .= $tmp['images'][0] . PHP_EOL;
        }

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
