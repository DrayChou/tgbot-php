<?php

/**
 * 通过 pixabay 的接口搜索图片
 * @Author: dray
 * @Date:   2016-03-19 23:46:28
 * @Last Modified by:   zhouw
 * @Last Modified time: 2016-03-20 00:00:42
 */

class Pixabay extends Base
{
    public static function desc()
    {
        return '/img - Search image with Pixabay API and sends it. ';
    }

    public static function usage()
    {
        return array(
            '/img info: Random search an image with Pixabay API.',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数.
     */
    public function run()
    {
        Common::echo_log('执行 Pixabay run');

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $data = array(
            'key' => '2247422-5a682bb78206ac4882ff8954a',
            'image_type' => 'all',
            'lang' => 'zh',
            'orientation' => 'all',
            'safesearch' => 'false',
            'order' => 'latest',
            'page' => '1',
            'per_page' => '5',
            'pretty' => 'false',
            'q' => $this->text,
        );
        $url = 'https://pixabay.com/api/?' . http_build_query($data);
        $res = Common::curl($url);

        if (!isset($res['hits'])) {
            $res_str = 'api error!';
        } else {
            $rand_key = array_rand($res['hits']);
            $rand_arr = $res['hits'][$rand_key];
            $res_str = ($rand_arr['webformatURL'] ? $rand_arr['webformatURL'] : $rand_arr['previewURL']) . PHP_EOL;
        }

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
