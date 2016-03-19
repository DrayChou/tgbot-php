<?php

/**
 * 天气预报
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Weather extends Base
{
    static function desc() {
        return "/weather - weather in that city";
    }

    static function usage() {
        return array(
            "/weather (city)"
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     * @throws Exception
     */
    public function run() {
        CFun::echo_log("Weather run 执行");

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $data = array(
            'units' => 'metric',
            'q'     => $this->text,
        );
        $url  = "http://api.openweathermap.org/data/2.5/weather?" . http_build_query($data);
        $res  = CFun::curl($url);

        if (!isset($res['name'])) {
            $res_str = 'Can\'t get weather from that city.';
        } else {
            $res_str = "The temperature in {$res['name']} ({$res['sys']['country']}) is {$res['main']['temp']} °C" . PHP_EOL;
            $res_str .= "Current conditions are: {$res['weather'][0]['description']} {$res['weather'][0]['main']}";
        }

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
