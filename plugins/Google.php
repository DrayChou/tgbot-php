<?php

/**
 * 谷歌搜索接口
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Google extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return "/google - Searches Google and send results.";
    }

    /**
     * 命令操作详解
     * Detailed command operation
     * @return array
     */
    public static function usage()
    {
        return array(
            "/google - Searches Google and send results.",
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
            // '/google',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     * @throws Exception
     */
    public function run()
    {
        Common::echo_log("Google run 执行");

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $data = array(
            'v' => '1.0',
            'q' => $this->text,
        );
        $url = "http://ajax.googleapis.com/ajax/services/search/web?" . http_build_query($data);
        $res = Common::curl($url);

        $res_str = '';
        if (!isset($res['responseStatus']) || $res['responseStatus'] != 200) {
            $res_str = $res['responseDetails'];
        } else {
            foreach ($res['responseData']['results'] as $v) {
                $res_str = $res_str . $v['titleNoFormatting'] . ' - ' . ($v['unescapedUrl'] ? $v['unescapedUrl'] : $v['url']) . PHP_EOL;
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
