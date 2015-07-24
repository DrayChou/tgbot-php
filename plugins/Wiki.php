<?php

/**
 * wiki 查询
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Wiki extends Base
{
    static function desc() {
        return "/wiki - Searches Wikipedia and send results";
    }

    static function usage() {
        return array(
            "/wiki [text]: Read extract from default Wikipedia (EN)",
            "/wiki(lang) [text]: Read extract from 'lang' Wikipedia. Example: !wikies hola",
            "/wiki search [text]: Search articles on default Wikipedia (EN)",
            "/wiki(lang) search [text]: Search articles on 'lang' Wikipedia. Example: !wikies search hola",
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     * @throws Exception
     */
    public function run() {
        CFun::echo_log("执行 Wiki run ");

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $is_search = false;

        if ($is_search) {
            $data = array(
                'format'   => 'json',
                'action'   => 'query',
                'list'     => 'search',
                'srlimit'  => '20',
                'continue' => '',
                'srsearch' => $this->text,
            );
        } else {
            $data = array(
                'format'          => 'json',
                'action'          => 'query',
                'prop'            => 'search',
                'srlimit'         => 'extracts',
                'exchars'         => '300',
                'redirects'       => 1,
                'exsectionformat' => 'plain',
                'explaintext'     => '',
                'titles'          => $this->text,
            );
        }

        $url = "https://en.wikipedia.org/w/api.php?" . http_build_query($data);
        $res = CFun::curl($url);

        $res_str = '';
        if (!isset($res['query'])) {
            $res_str = '好像出问题了，稍后再试下吧！';
        } else {
            if ($is_search) {
                foreach ($res['query']['search'] as $v) {
                    $res_str .= $v['title'] . PHP_EOL;
                }
            } else {
                foreach ($res['query']['pages'] as $v) {
                    $res_str .= $v['extract'] . PHP_EOL;
                }
            }
        }

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
