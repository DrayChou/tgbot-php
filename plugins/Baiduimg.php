<?php

/**
 * 百度搜索图片接口
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43.
 */
class Baiduimg extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        array(
            '/baiduimg - Random search an image with Baidu API.',
            '/百度搜图 - 使用百度搜索相关的图片.',
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
            '/baiduimg info - Random search an image with Baidu API.',
            '/百度搜图 关键词 - 使用百度搜索相关的图片.',
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
            '/baiduimg',
            '/百度搜图'
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数.
     */
    public function run()
    {
        Common::echo_log('执行 BaiduImg run');

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $data = array(
            'tn' => 'resultjson_com',
            'ipn' => 'rj',
            'ct' => '201326592',
            'fp' => 'result',
            'cl' => '2',
            'adpicid' => '',
            'istype' => '2',
            'word' => $this->text,
        );
        $url = 'image.baidu.com/search/acjson?' . http_build_query($data);
        $res = Common::curl($url);

        if (!isset($res['data'])) {
            $res_str = 'api error!';
        } else {
            $rand_key = array_rand($res['data']);
            $rand_arr = $res['data'][$rand_key];
            $res_str = ($rand_arr['middleURL'] ? $rand_arr['middleURL'] : $rand_arr['thumbURL']) . PHP_EOL;
        }

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
