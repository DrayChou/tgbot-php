<?php

/**
 * 天气预报
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Weather extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/weather - weather in that city",
            '/天气 - 查询天气状况',
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
            "/weather (city) - weather in that city",
            '/天气 Shanghai - 查询 shanghai 天气状况',
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
            '/weather',
            '/天气',
        );
    }

    /**
     * 返回 中国 天气对应的代码
     * http://mobile.weather.com.cn/js/citylist.xml
     * @param  [type] $city_name [description]
     * @return [type]            [description]
     */
    public static function get_weathercn_id($city_name)
    {
        $bot = Db::get_bot_name();
        $redis = Db::get_redis();

        $city_name = trim($city_name);

        $city_id = null;
        $db_city_id = "{$bot}config:weather_com_cn_citylist";
        $city_str = $redis->hGet($db_city_id, $city_name);
        $city_info = json_decode($city_str, true);

        //查看有没有数据
        if (empty($city_info)) {

            //如果没有任何数据
            $tmp_str = $redis->hGet($db_city_id, '上海');
            $tmp_info = json_decode($tmp_str, true);

            if (empty($tmp_info)) {
                //那么初始化
                $url = "http://mobile.weather.com.cn/js/citylist.xml";
                $str = Common::curl($url, null, 'xml');
                preg_match_all('/d1="([0-9]+)" d2="([^"]+)" d3="([^"]+)" d4="([^"]+)"/', $str, $match);

                $city_id_list = array();
                for ($i = 0; $i < count($match[0]); $i++) {
                    $city_id_list[$match[2][$i]] = array(
                        $match[1][$i],
                        $match[2][$i],
                        $match[3][$i],
                        $match[4][$i],
                    );
                }

                foreach ($city_id_list as $k => $v) {
                    $redis->hSet($db_city_id, $k, json_encode($v));

                    if ($k == $city_name) {
                        $city_id = $v[0];
                    }
                }

                Common::echo_log("get_weathercn_id: city_id_list=%s", print_r($city_id_list, true));
            }
        } else {
            $city_id = $city_info[0];
        }

        return $city_id;
    }

    /**
     * 通过中国天气获取天气信息
     * http://mobile.weather.com.cn/data/sk/102030100.html?_=1381891661455
     * @param  [type] $city_id [description]
     * @return [type]          [description]
     */
    public static function get_by_weathercn($city_id)
    {
        $res_str = null;
        $url = "http://mobile.weather.com.cn/data/sk/{$city_id}.html?_=" . time();
        $res = Common::curl($url);

        if (isset($res['sk_info'])) {
            $sk = $res['sk_info'];
            $res_str = "{$sk['cityName']} {$sk['temp']}°C {$sk['wd']}{$sk['ws']} 湿度{$sk['sd']} {$sk['sm']}";
        }

        return $res_str;
    }

    /**
     * 通过 openweathermap 获取天气信息
     * http://www.openweathermap.org/
     * @param  [type] $city [description]
     * @return [type]       [description]
     */
    public static function get_by_openweathermap($city)
    {
        $res_str = null;
        $data = array(
            'units' => 'metric',
            'APPID' => 'd6ef0178ef9d6d2c2fae60f4863c7e17',
            'q' => $city,
        );
        $url = "http://api.openweathermap.org/data/2.5/weather?" . http_build_query($data);
        $res = Common::curl($url);

        if (!isset($res['name'])) {
            $res_str = 'Can\'t get weather from that city.';
        } else {
            $res_str = "The temperature in {$res['name']} ({$res['sys']['country']}) is {$res['main']['temp']} °C" . PHP_EOL;
            $res_str .= "Current conditions are: {$res['weather'][0]['description']} {$res['weather'][0]['main']}";
        }

        return $res_str;
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     * @throws Exception
     */
    public function run()
    {
        Common::echo_log("Weather run 执行");

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        $city_id = self::get_weathercn_id($this->text);
        if (!empty($city_id)) {
            $res_str = self::get_by_weathercn($city_id);
        } else {
            $res_str = self::get_by_openweathermap($this->text);
        }

        if (empty($res_str)) {
            $res_str = 'Can\'t get weather from that city.';
        }

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
