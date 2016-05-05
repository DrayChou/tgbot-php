<?php
/**
 * 通过 Giphy 的接口搜索图片
 * @Author: dray
 * @Date:   2016-03-19 23:46:28
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-04 15:19:26
 */

class Giphy extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            '/giphy - Search image with Giphy API and sends it. ',
            // '/img - Search image with Giphy API and sends it. ',
        );
    }

    /**
     * 命令操作详解
     * Detailed command operation
     * @return array
     */
    public static function usage()
    {
        return array(
            '/giphy info - Random search an image with Giphy API.',
            // '/img info - Random search an image with Giphy API.',
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
            '/giphy',
            // '/img',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数.
     */
    public function run()
    {
        Common::echo_log('执行 Giphy run');

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $data = array(
            'api_key' => 'dc6zaTOxFJmzC',
            'limit' => '5',
            'offset' => '0',
            'rating' => 'y',
            'fmt' => 'json',
            'q' => $this->text,
        );
        $url = 'http://api.giphy.com/v1/gifs/search?' . http_build_query($data);
        $res = Common::curl($url);

        if (!isset($res['data'])) {
            $res_str = 'api error!';
        } else {
            $rand_key = array_rand($res['data']);
            $rand_arr = $res['data'][$rand_key];

            if (isset($rand_arr['images']) && isset($rand_arr['images']['original'])) {
                $res_str = $rand_arr['images']['original']['url'] . PHP_EOL;
            } else {
                $res_str = $rand_arr['url'] . PHP_EOL;
            }
        }

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
