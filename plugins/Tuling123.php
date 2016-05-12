<?php

/**
 * 图灵机器人的小接口
 * http://www.tuling123.com/
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Tuling123 extends Base
{
    const LIST_SHOW_MAX = 1;

    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/tuling123 - Dialogue with tuling123 robot.  ",
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
            "/tuling123 info: 请求图灵的机器人接口，并返回回答。",
            "Request Turing robot, and return the results. Only support Chinese.",
            "升级链接|Upgrade link:http://www.tuling123.com/openapi/record.do?channel=98150",
            "图灵机器人注册邀请地址，每有一个用户通过此地址注册账号，增加本接口可调用次数 1000次/天。",
            "Turing robot registration invitation address, each user has a registered account through this address, increase the number of calls this interface can be 1000 times / day. Translation from Google!",
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
            '/tuling123',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("Tuling123 run 执行");

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $res_str = '';
            $res_str .= '你想问什么呢？ E.g.:' . PHP_EOL;
            $res_str .= '今天星期几？' . PHP_EOL;
            $res_str .= PHP_EOL;
            $res_str .= "你也可以下面的方式更快的进行提问: " . PHP_EOL;
            $res_str .= join(PHP_EOL, self::desc());

            $this->set_reply($res_str);
            return;
        }

        $data = array(
            'key' => '4d546ffb4cd27187ef2a20d3af54c5b6',
            'userid' => $this->from_id,
            'info' => $this->text,
        );
        $url = "http://www.tuling123.com/openapi/api?" . http_build_query($data);
        $res = Common::curl($url);

        $res_str = $res['text'];

        //如果有链接
        if (isset($res['url'])) {
            $res_str = $res_str . "\n" . $res_str . " " . $res['url'];
        }

        //如果是新闻
        if ($res['code'] == 302000) {
            foreach ($res['list'] as $k => $v) {
                if ($k >= self::LIST_SHOW_MAX) {
                    break;
                }

                $res_str = $res_str . "\n 标题:" . $v['article'];
                $res_str = $res_str . "\n 来源:" . $v['source'];
                $res_str = $res_str . "\n" . $v['detailurl'];
                $res_str = $res_str . "\n" . $v['icon'];
            }
        }

        //如果是菜谱
        if ($res['code'] == 308000) {
            foreach ($res['list'] as $k => $v) {
                if ($k >= self::LIST_SHOW_MAX) {
                    break;
                }

                $res_str = $res_str . "\n 名称:" . $v['name'];
                $res_str = $res_str . "\n 详情:" . $v['info'];
                $res_str = $res_str . "\n" . $v['detailurl'];
                $res_str = $res_str . "\n" . $v['icon'];
            }
        }

        //如果是列车
        if ($res['code'] == 305000) {
            foreach ($res['list'] as $k => $v) {
                if ($k >= self::LIST_SHOW_MAX) {
                    break;
                }

                $res_str = $res_str . "\n 车次:" . $v['trainnum'];
                $res_str = $res_str . "\n 起始站:" . $v['start'];
                $res_str = $res_str . "\n 到达站:" . $v['terminal'];
                $res_str = $res_str . "\n 开车时间:" . $v['starttime'];
                $res_str = $res_str . "\n 到达时间:" . $v['endtime'];
                $res_str = $res_str . "\n" . $v['detailurl'];
                $res_str = $res_str . "\n" . $v['icon'];
            }
        }

        //如果是航班
        if ($res['code'] == 306000) {
            foreach ($res['list'] as $k => $v) {
                if ($k >= self::LIST_SHOW_MAX) {
                    break;
                }

                $res_str = $res_str . "\n 航班:" . $v['flight'];
                $res_str = $res_str . "\n 航班路线:" . $v['route'];
                $res_str = $res_str . "\n 航班状态:" . $v['state'];
                $res_str = $res_str . "\n 开车时间:" . $v['starttime'];
                $res_str = $res_str . "\n 到达时间:" . $v['endtime'];
                $res_str = $res_str . "\n" . $v['detailurl'];
                $res_str = $res_str . "\n" . $v['icon'];
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
