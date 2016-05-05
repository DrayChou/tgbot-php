<?php

/**
 * 搜索百度盘资源
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Googlebaidupan extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/bp - Searches in pan of baidu by Google and send results.",
            "/度盘 - 用 Google 搜索百度云盘暴露出来的文件。",
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
            "/bp [info]- Searches in pan of baidu by Google and send results.",
            "/度盘 [info] - 用 Google 搜索百度云盘暴露出来的文件。",
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
            '/bp',
            '/度盘',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     * @throws Exception
     */
    public function run()
    {
        Common::echo_log("GoogleBaiduPan run 执行");

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $data = array(
            'key' => 'AIzaSyACNEu_BDGyBwZiQjZ5fw3ksHzo56FeoGA',
            'cx' => '010607825858754423132:z7eh-8uygee',
            'q' => $this->text,
        );
        $url = "https://www.googleapis.com/customsearch/v1?" . http_build_query($data);
        $res = Common::curl($url);

        $res_str = '';
        if (!empty($res['items'])) {
            foreach ($res['items'] as $v) {
                $res_str = $res_str . $v['title'] . ' - ' . $v['link'] . PHP_EOL;
            }
        }

        if (empty($res_str)) {
            $res_str = 'api error!';
        }

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
