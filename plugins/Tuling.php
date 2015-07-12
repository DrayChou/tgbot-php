<?php

/**
 * 图灵机器人的小接口
 * http://www.tuling123.com/
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Tuling extends Base {

    static function desc() {

        return "/bot - 询问图灵小机器人.  ";
    }

    static function usage() {
        return array(
            "/bot info: 请求图灵的机器人接口，并返回回答。",
            "Request Turing robot, and return the results. Only support Chinese.",
            "升级链接|Upgrade link:http://www.tuling123.com/openapi/record.do?channel=98150",
            "图灵机器人注册邀请地址，每有一个用户通过此地址注册账号，增加本接口可调用次数 1000次/天。",
            "Turing robot registration invitation address, each user has a registered account through this address, increase the number of calls this interface can be 1000 times / day. Translation from Google!"
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        CommonFunction::echo_log("执行 Bot run");

        $tuling_config = CommonFunction::get_config('tuling');
        if (!empty($tuling_config['key'])) {
            throw new Exception('error tuling bot key!');
        }

        $url  = "http://www.tuling123.com/openapi/api";
        $data = array(
            'key'    => $tuling_config['key'],
            'info'   => $this->text,
            'userid' => $this->from_id,
        );

        $res = CommonFunction::post($url, $data);
        CommonFunction::echo_log("发送 bot 查询: res=%s", $res);

        $res_str = $res['text'];

        //如果有链接
        if (isset($res['url'])) {
            $res_str = $res_str . "\n" . $res_str . " " . $res['url'];
        }

        //如果是新闻
        if ($res['code'] == 302000) {
            foreach ($res['list'] as $k => $v) {
                $res_str = $res_str . "\n 标题:" . $v['article'];
                $res_str = $res_str . "\n 来源:" . $v['source'];
                $res_str = $res_str . "\n" . $v['detailurl'];
                $res_str = $res_str . "\n" . $v['icon'];

                if ($k >= 3) {
                    break;
                }
            }
        }

        //如果是菜谱
        if ($res['code'] == 308000) {
            foreach ($res['list'] as $k => $v) {
                $res_str = $res_str . "\n 名称:" . $v['name'];
                $res_str = $res_str . "\n 详情:" . $v['info'];
                $res_str = $res_str . "\n" . $v['detailurl'];
                $res_str = $res_str . "\n" . $v['icon'];

                if ($k >= 3) {
                    break;
                }
            }
        }


        //如果是列车
        if ($res['code'] == 305000) {
            foreach ($res['list'] as $k => $v) {
                $res_str = $res_str . "\n 车次:" . $v['trainnum'];
                $res_str = $res_str . "\n 起始站:" . $v['start'];
                $res_str = $res_str . "\n 到达站:" . $v['terminal'];
                $res_str = $res_str . "\n 开车时间:" . $v['starttime'];
                $res_str = $res_str . "\n 到达时间:" . $v['endtime'];
                $res_str = $res_str . "\n" . $v['detailurl'];
                $res_str = $res_str . "\n" . $v['icon'];

                if ($k >= 3) {
                    break;
                }
            }
        }

        //如果是航班
        if ($res['code'] == 306000) {
            foreach ($res['list'] as $k => $v) {
                $res_str = $res_str . "\n 航班:" . $v['flight'];
                $res_str = $res_str . "\n 航班路线:" . $v['route'];
                $res_str = $res_str . "\n 航班状态:" . $v['state'];
                $res_str = $res_str . "\n 开车时间:" . $v['starttime'];
                $res_str = $res_str . "\n 到达时间:" . $v['endtime'];
                $res_str = $res_str . "\n" . $v['detailurl'];
                $res_str = $res_str . "\n" . $v['icon'];

                if ($k >= 3) {
                    break;
                }
            }
        }

        //回复消息
        $msg = Telegram::singleton()->post('sendMessage', array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));

        CommonFunction::echo_log("发送信息: msg=%s", $msg);
    }

}
