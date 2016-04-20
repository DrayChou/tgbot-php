<?php

/**
 * 从 thumblr 中获取图片展示出来
 * @Author: dray
 * @Date:   2016-04-20 20:06:13
 * @Last Modified by:   dray
 * @Last Modified time: 2016-04-20 20:58:30
 */

class Tumblr extends Base
{
    public static function desc()
    {
        return "/tumblr - Get a image from tumblr. ";
    }

    public static function usage()
    {
        return array(
            "/tumblr [blog url] - Get a image from tumblr. ",
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("Tumblr run 执行");

        $bot = Db::get_bot_name();
        $redis = Db::get_redis();

        $blog_arr_key = $bot . 'config:thumblr';

        //可以查询的博客地址
        $base_blog_arr = array(
            'jumpinggirlsession.tumblr.com',
            'carudamon119.tumblr.com',
            'wonderwall99999.tumblr.com',
            'nanamizm.tumblr.com',
            'beatutifulwoman.tumblr.com',
            'kusanoryu.tumblr.com',
            'phorbidden.tumblr.com',
            'uchida4649.tumblr.com',
            'czzoo.tumblr.com',
            'tetu0831.tumblr.com',
            'asagaonosakukisetu.tumblr.com',
            'renatakeda.tumblr.com',
            'kpivy8.tumblr.com',
            'fukunono22.tumblr.com',
        );
        if (0 == $redis->sCard($blog_arr_key)) {
            foreach ($base_blog_arr as $v) {
                $redis->sAdd($blog_arr_key, $v);
            }
        }

        // 生成 Blog 地址
        if (empty($this->text)) {
            $blog_url = sRandMember($blog_arr_key);
        } else {
            $tmp = parse_url($this->text, PHP_URL_HOST);
            if (empty($tmp)) {
                $blog_url = $this->text;
            } else {
                $blog_url = $tmp;
            }

            $tmp = explode('.', $blog_url);
            if (count($tmp) == 1) {
                $blog_url .= '.tumblr.com';
            }
        }

        // 查询
        $data = array(
            'api_key' => 'PAI0ehMRq9LJQienTSyk934REZ8z9tbEz8hKZSrgDBOukv49Oz',
            'type' => 'photo',
            'notes_info' => 'false',
        );
        $url = "https://api.tumblr.com/v2/blog/{$blog_url}/posts?" . http_build_query($data);
        $res = Common::curl($url);

        $res_str = 'Cannot get that blog, trying another one...';
        if (isset($res['meta']) && isset($res['meta']['status']) && $res['meta']['status'] == 200) {
            if (isset($res['response']) && isset($res['response']['posts'])) {

                $posts = $res['response']['posts'];
                $post = $posts[array_rand($posts)];

                $res_str = $post['slug'] . ' - ' . $post['photos']['original_size']['url'];
            }
        }

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id' => $this->from_id,
            'text' => $res_str,
            // 'reply_to_message_id' => $this->msg_id,
        ));
    }
}
