<?php

/**
 * 管理工具
 * User: dray
 * Date: 15/7/30
 * Time: 下午3:43
 */
class Admin extends Base
{
    static $ADMIN_MAP = array(
        1 => 'reset_routing',
        2 => 'sys_info',
    );

    static function desc() {
        return "/admin - admin";
    }

    static function usage() {
        return array(
            "/admin - admin",
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        CFun::echo_log("Admin run 执行");

        $admins = CFun::get_config('admins');
        if (!in_array($this->from_id, $admins)) {
            return;
        }

        $do_   = false;
        $parms = array();
        foreach ($this->parms as $k => $v) {
            $is_flg = false;
            foreach (self::$ADMIN_MAP as $n) {
                if (0 == strcasecmp($v, $n)) {
                    $do_    = $n;
                    $is_flg = true;
                    break;
                }
            }

            if ($is_flg) {
                continue;
            }

            $parms[] = $v;
        }

        if (empty($do_)) {
            $key_board = NULL;
            foreach (self::$ADMIN_MAP as $v) {
                $key_board[] = array(
                    '/admin ' . $v,
                );
            }

            //发送
            Telegram::singleton()->send_message(array(
                'chat_id'             => $this->chat_id,
                'text'                => '请选择你要使用的功能！' . PHP_EOL . '目前支持：' . PHP_EOL . implode(PHP_EOL, self::$ADMIN_MAP) . PHP_EOL,
                'reply_to_message_id' => $this->msg_id,
                'reply_markup'        => json_encode(array(
                    'keyboard'          => $key_board,
                    'resize_keyboard'   => true,
                    'one_time_keyboard' => true,
                    'selective'         => true,
                )),
            ));

            return;
        }

        $res_str = '操作完成，亲！';
        switch ($do_) {
            case 1: {
                Db::get_router(true);
                break;
            }
            case 2: {
                $res_str = print_r($this->sys_linux(), true);
                break;
            }
        }

        //发送
        Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }

    //linux系统探测
    function sys_linux() {
        // CPU
        if (false === ($str = @file("/proc/cpuinfo"))) return false;
        $str = implode("", $str);
        @preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model);
        @preg_match_all("/cpu\s+MHz\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $mhz);
        @preg_match_all("/cache\s+size\s{0,}\:+\s{0,}([\d\.]+\s{0,}[A-Z]+[\r\n]+)/", $str, $cache);
        @preg_match_all("/bogomips\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $bogomips);
        if (false !== is_array($model[1])) {
            $res['cpu']['num'] = sizeof($model[1]);
            /*
            for($i = 0; $i < $res['cpu']['num']; $i++)
            {
                $res['cpu']['model'][] = $model[1][$i].'&nbsp;('.$mhz[1][$i].')';
                $res['cpu']['mhz'][] = $mhz[1][$i];
                $res['cpu']['cache'][] = $cache[1][$i];
                $res['cpu']['bogomips'][] = $bogomips[1][$i];
            }*/
            if ($res['cpu']['num'] == 1)
                $x1 = '';
            else
                $x1 = ' ×' . $res['cpu']['num'];
            $mhz[1][0]             = ' | 频率:' . $mhz[1][0];
            $cache[1][0]           = ' | 二级缓存:' . $cache[1][0];
            $bogomips[1][0]        = ' | Bogomips:' . $bogomips[1][0];
            $res['cpu']['model'][] = $model[1][0] . $mhz[1][0] . $cache[1][0] . $bogomips[1][0] . $x1;
            if (false !== is_array($res['cpu']['model'])) $res['cpu']['model'] = implode("<br />", $res['cpu']['model']);
            if (false !== is_array($res['cpu']['mhz'])) $res['cpu']['mhz'] = implode("<br />", $res['cpu']['mhz']);
            if (false !== is_array($res['cpu']['cache'])) $res['cpu']['cache'] = implode("<br />", $res['cpu']['cache']);
            if (false !== is_array($res['cpu']['bogomips'])) $res['cpu']['bogomips'] = implode("<br />", $res['cpu']['bogomips']);
        }

        // NETWORK

        // UPTIME
        if (false === ($str = @file("/proc/uptime"))) return false;
        $str   = explode(" ", implode("", $str));
        $str   = trim($str[0]);
        $min   = $str / 60;
        $hours = $min / 60;
        $days  = floor($hours / 24);
        $hours = floor($hours - ($days * 24));
        $min   = floor($min - ($days * 60 * 24) - ($hours * 60));
        if ($days !== 0) $res['uptime'] = $days . "天";
        if ($hours !== 0) $res['uptime'] .= $hours . "小时";
        $res['uptime'] .= $min . "分钟";

        // MEMORY
        if (false === ($str = @file("/proc/meminfo"))) return false;
        $str = implode("", $str);
        preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
        preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);

        $res['memTotal']   = round($buf[1][0] / 1024, 2);
        $res['memFree']    = round($buf[2][0] / 1024, 2);
        $res['memBuffers'] = round($buffers[1][0] / 1024, 2);
        $res['memCached']  = round($buf[3][0] / 1024, 2);
        $res['memUsed']    = $res['memTotal'] - $res['memFree'];
        $res['memPercent'] = (floatval($res['memTotal']) != 0) ? round($res['memUsed'] / $res['memTotal'] * 100, 2) : 0;

        $res['memRealUsed']    = $res['memTotal'] - $res['memFree'] - $res['memCached'] - $res['memBuffers']; //真实内存使用
        $res['memRealFree']    = $res['memTotal'] - $res['memRealUsed']; //真实空闲
        $res['memRealPercent'] = (floatval($res['memTotal']) != 0) ? round($res['memRealUsed'] / $res['memTotal'] * 100, 2) : 0; //真实内存使用率

        $res['memCachedPercent'] = (floatval($res['memCached']) != 0) ? round($res['memCached'] / $res['memTotal'] * 100, 2) : 0; //Cached内存使用率

        $res['swapTotal']   = round($buf[4][0] / 1024, 2);
        $res['swapFree']    = round($buf[5][0] / 1024, 2);
        $res['swapUsed']    = round($res['swapTotal'] - $res['swapFree'], 2);
        $res['swapPercent'] = (floatval($res['swapTotal']) != 0) ? round($res['swapUsed'] / $res['swapTotal'] * 100, 2) : 0;

        // LOAD AVG
        if (false === ($str = @file("/proc/loadavg"))) return false;
        $str            = explode(" ", implode("", $str));
        $str            = array_chunk($str, 4);
        $res['loadAvg'] = implode(" ", $str[0]);

        return $res;
    }
}