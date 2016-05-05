<?php

/**
 * github 账户查询
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Github extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/github - get github user info",
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
            "/github - get github user info",
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
            '/github',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     * @throws Exception
     */
    public function run()
    {
        Common::echo_log("Github run 执行");

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $url = "https://api.github.com/users/" . $this->text;
        $res = Common::curl($url);

        if (!isset($res['id'])) {
            $res_str = 'Can\'t get user info from Github.';
        } else {
            $res_str = '';
            $res_str .= "ID: {$res['login']}" . PHP_EOL;
            $res_str .= "Type: {$res['type']}" . PHP_EOL;
            $res_str .= "Name: {$res['name']}" . PHP_EOL;
            $res_str .= "Company: {$res['company']}" . PHP_EOL;
            $res_str .= "Email: {$res['email']}" . PHP_EOL;
            $res_str .= "Repos: {$res['public_repos']}" . PHP_EOL;
            $res_str .= "Gists: {$res['public_gists']}" . PHP_EOL;
            $res_str .= "Followers: {$res['followers']}" . PHP_EOL;
            $res_str .= "Following: {$res['following']}" . PHP_EOL;
            $res_str .= "Url: {$res['html_url']}" . PHP_EOL;
        }

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
