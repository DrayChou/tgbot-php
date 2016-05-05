<?php

/**
 * 用户在线状态接口
 * User: dray
 * Date: 15/7/13
 * Time: 下午3:26
 */
class Stats extends Base
{
    const DEFAULT_SHOW_LIMIT = 10;
    const NUM_MSG_MAX = 5;
    const TIME_CHECK = 4;

    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/stats - Returns a list of Username [telegram_id]: msg_num only top." . self::DEFAULT_SHOW_LIMIT,
            "/统计 - 显示发言数统计结果，显示前." . self::DEFAULT_SHOW_LIMIT,
            "/state - Returns this user All days stats.",
            "/状态 - 显示具体用户发言数.",
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
            "/stats - Returns a list of Username [telegram_id]: msg_num only top." . self::DEFAULT_SHOW_LIMIT,
            "/stats 20150528 - Returns this day stats",
            "/stats all: Returns All days stats.",
            "/stats 20150528 " . self::DEFAULT_SHOW_LIMIT . ": Returns a list only top " . self::DEFAULT_SHOW_LIMIT,
            "/state user_id: Returns this user All days stats",
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
            '/stats',
            '/统计',
            '/state',
            '/状态',
        );
    }

    /**
     * 重建每日数据
     * [data_to_2 description]
     * @return [type] [description]
     */
    private function rebuild_day_msgs()
    {
        $bot = Db::get_bot_name();
        $redis = Db::get_redis();

        $day_keys = array();
        $user_keys = array();
        $sum = 0;

        $days_list = $redis->keys("{$bot}stats:chat_user_day_msgs:*:*");
        foreach ($days_list as $k => $v) {
            $keys = explode(':', $v);
            $chat_id = $keys[4];
            $day_id = $keys[5];

            if (!is_numeric($day_id) || !is_numeric($chat_id)) {
                Common::echo_log("data_to_2 无效ID，跳过: {$v}");
                continue;
            }

            $msg_ls = $redis->zRevRange("{$bot}stats:chat_user_day_msgs:{$chat_id}:{$day_id}", 0, -1, true);
            foreach ($msg_ls as $user_id => $count) {
                //如果是第一次记录，那么删除之前的数据
                if (!isset($day_keys[$day_id])) {
                    $day_keys[$day_id] = 1;
                    $redis->zDelete($bot . 'stats:chat_day_msgs:' . $chat_id, $day_id);
                }

                // 记录群组每天的发言数
                $redis->zIncrBy($bot . 'stats:chat_day_msgs:' . $chat_id, $count, $day_id);

                //如果是第一次记录，那么删除之前的数据
                if (!isset($user_keys[$user_id])) {
                    $user_keys[$user_id] = 1;
                    $redis->zDelete($bot . 'stats:chat_user_msgs:' . $chat_id, $user_id);
                }

                // 记录群组每天的发言数
                $redis->zIncrBy($bot . 'stats:chat_user_msgs:' . $chat_id, $count, $user_id);

                $sum += 1;
            }

            Common::echo_log("data_to_2 处理: {$v}");
        }

        $text[] = '重建数据:' . $sum;

        return join(PHP_EOL, $text);
    }

    /**
     * 删除旧版无效的数据
     * [data_to_2 description]
     * @return [type] [description]
     */
    private function data_d1()
    {
        $bot = Db::get_bot_name();
        $redis = Db::get_redis();

        $s1 = 0;
        $key1 = "chat:*";
        $days_list = $redis->keys($key1);
        foreach ($days_list as $k => $v) {
            //删除这个键
            $redis->delete($v);
            $s1 += 1;
        }

        $text[] = '删除数据:' . $s1;

        $s1 = 0;
        $key1 = "msgs:*";
        $days_list = $redis->keys($key1);
        foreach ($days_list as $k => $v) {
            //删除这个键
            $redis->delete($v);
            $s1 += 1;
        }

        $text[] = '删除数据:' . $s1;

        $s1 = 0;
        $key1 = "stats:*";
        $days_list = $redis->keys($key1);
        foreach ($days_list as $k => $v) {
            //删除这个键
            $redis->delete($v);
            $s1 += 1;
        }

        $text[] = '删除数据:' . $s1;

        $s1 = 0;
        $key1 = "user:*";
        $days_list = $redis->keys($key1);
        foreach ($days_list as $k => $v) {
            //删除这个键
            $redis->delete($v);
            $s1 += 1;
        }

        $text[] = '删除数据:' . $s1;

        return join(PHP_EOL, $text);
    }

    /**
     * 得到群最热闹的一天和最不热闹的一天的数据
     * @param $chat_id
     * @return array
     */
    private function get_chat_mx($chat_id)
    {
        $bot = Db::get_bot_name();
        $redis = Db::get_redis();

        $key1 = $bot . 'stats:chat_day_msgs:' . $this->chat_id;
        $max_day = $redis->zRevRange($key1, 0, 1);
        Common::echo_log("Stats: {$key1}=%s", print_r($max_day, true));

        $key2 = $bot . 'stats:chat_day_msgs:' . $this->chat_id;
        $min_day = $redis->zRange($key2, 0, 1);
        Common::echo_log("Stats: {$key2}=%s", print_r($min_day, true));

        $max_msgs = (int) $redis->zScore($bot . 'stats:chat_day_msgs:' . $this->chat_id, $max_day[0]);
        $min_msgs = (int) $redis->zScore($bot . 'stats:chat_day_msgs:' . $this->chat_id, $min_day[0]);

        return array('mxd' => $max_day[0], 'mxm' => $max_msgs, 'mid' => $min_day[0], 'mim' => $min_msgs);
    }

    /**
     * 得到这个群组中所有的用户和聊天记录信息
     * @param $chat_id
     * @param null $day_id
     * @return array
     */
    private function get_chat_users($chat_id, $day_id = null)
    {
        $bot = Db::get_bot_name();
        $redis = Db::get_redis();

        //预处理
        if (empty($day_id)) {
            $day_id = date('Ymd');
        } elseif (!is_numeric($day_id)) {
            if (in_array(strtolower($day_id), array('a', 'all', '*'))) {
                $day_id = '*';
            } elseif (in_array(strtolower($day_id), array('t', 'tdy', 'today'))) {
                $day_id = date('Ymd');
            }
        }

        $res = array();

        $key = $bot . 'stats:chat_user_day_msgs:' . $this->chat_id . ':' . $day_id;
        $key_list = $redis->keys($key);
        Common::echo_log("Stats: {$key}=%s", print_r($key_list, true));

        foreach ($key_list as $k => $v) {
            $msg_ls = $redis->zRevRange($v, 0, -1, true);

            foreach ($msg_ls as $id => $msgs) {
                if (isset($res[$id])) {
                    $res[$id] += $msgs;
                } else {
                    $res[$id] = $msgs;
                }
            }
        }

        arsort($res);

        return $res;
    }

    /**
     * 得到群的聊天状况
     * @param $chat_id
     * @param $day_id
     * @param $limit
     * @return string
     */
    private function get_chat_stats($chat_id, $day_id, $limit)
    {
        $bot = Db::get_bot_name();
        $redis = Db::get_redis();

        $uses_info = $this->get_chat_users($chat_id, $day_id);
        if ($limit < 0) {
            asort($users_info);
        }

        $text[] = strtoupper($day_id) . ' TOP ' . $limit;

        $top_sum = 0;
        $all_sum = 0;
        foreach ($uses_info as $user_id => $msgs) {
            $all_sum += $msgs;

            if (count($text) <= $limit) {
                $top_sum += $msgs;
                $text[] = ($redis->hGet($bot . 'users:ids', $user_id) . ' => ' . $msgs);
            }
        }

        $chat_max = $this->get_chat_mx($chat_id);

        $text[] = 'top sum:' . $top_sum;
        $text[] = 'all sum:' . $all_sum;
        $text[] = 'top/all:' . intval($top_sum / ($all_sum == 0 ? 1 : $all_sum) * 100) . '%';
        $text[] = 'max day:' . ($chat_max['mxd'] . ' => ' . $chat_max['mxm']);
        $text[] = 'min day:' . ($chat_max['mid'] . ' => ' . $chat_max['mim']);

        return join(PHP_EOL, $text);
    }

    /**
     * 得到用户的聊天情况
     * @param  [type] $chat_id [description]
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    private function get_user_stats($chat_id, $user_id)
    {
        $bot = Db::get_bot_name();
        $redis = Db::get_redis();

        if ($user_id == 'me') {
            $user_id = $this->from_id;
        }

        if (!is_numeric($user_id)) {
            if ($tmp_id = $redis->hGet($bot . 'users:usernames', $user_id)) {
                $user_id = $tmp_id;
            }
        }

        $all_sum = 0;
        $user_sum = 0;
        $users_info = $this->get_chat_users($chat_id);
        foreach ($users_info as $id => $msgs) {
            $all_sum += $msgs;

            if ($id == $user_id) {
                $user_sum = (int) $msgs;
            }
        }

        $day_all_sum = 0;
        $day_user_sum = 0;
        $day_users_info = $this->get_chat_users($chat_id, date('Ymd'));
        foreach ($day_users_info as $id => $msgs) {
            $day_all_sum += $msgs;

            if ($id == $user_id) {
                $day_user_sum = (int) $msgs;
            }
        }

        $show_name = $redis->hGet($bot . 'users:ids', $user_id);

        $text = array();
        $text[] = $show_name . ' stats:';
        $text[] = 'stats count:' . $user_sum;
        $text[] = 'all user sum:' . $all_sum;
        $text[] = 'user/all:' . intval($user_sum / ($all_sum == 0 ? 1 : $all_sum) * 100) . '%';
        $text[] = 'user today count:' . $day_user_sum;
        $text[] = 'all user today sum:' . $day_all_sum;
        $text[] = 'user/all:' . intval($day_user_sum / ($day_all_sum == 0 ? 1 : $day_all_sum) * 100) . '%';

        return join(PHP_EOL, $text);
    }

    /**
     * 不管什么情况都会执行的函数
     */
    public function pre_process()
    {
        Common::echo_log("统计数据 开始");

        if (empty($this->parm)) {
            Common::echo_log("统计数据 内容为空，跳过");

            return;
        }

        //如果不是群组聊天的话，跳过
        if ($this->chat_id >= 0) {
            Common::echo_log("统计数据 不是群组聊天，跳过");

            return;
        }

        //如果是本机器人说的话，忽略不计
        $bot_info = Db::get_bot_info();
        if ($this->from_id == $bot_info['id']) {
            Common::echo_log("统计数据 机器人自己发出的，跳过");

            return;
        }

        Common::echo_log("统计数据 更新数据");

        $bot = Db::get_bot_name();
        $redis = Db::get_redis();

        //记录发言人的信息
        $redis->hSet($bot . 'users:ids', $this->from_id, $this->from_name);
        $redis->hSet($bot . 'users:usernames', $this->from_name, $this->from_id);
        $redis->hSet($bot . 'users:list', $this->from_id, json_encode($this->from));

        // 添加用户ID到群组中
        $redis->sadd($bot . 'chat:' . $this->chat_id . ':users', $this->from_id);

        // // 记录用户在这个群组中的聊天数
        // $redis->incr($bot . 'msgs:' . $this->from_id . ':' . $this->chat_id);
        // // 记录用户在这个群组中的聊天数,这天
        // $redis->incr($bot . 'day_msgs:' . date('Ymd') . ':' . $this->from_id . ':' . $this->chat_id);

        // 记录群组每天的发言数
        $redis->zIncrBy($bot . 'stats:chat_day_msgs:' . $this->chat_id, 1, date('Ymd'));

        // 记录用户在这个群组中的发言数
        $redis->zIncrBy($bot . 'stats:chat_user_msgs:' . $this->chat_id, 1, $this->from_id);

        // 记录用户在这个群组中每天的发言数
        $redis->zIncrBy($bot . 'stats:chat_user_day_msgs:' . $this->chat_id . ':' . date('Ymd'), 1, $this->from_id);

        Common::echo_log("统计数据 数据更新完毕");
    }

    /**
     * 当有人进入群的时候
     */
    public function msg_enter_chat()
    {
        Common::echo_log("有人进入群");

        $bot = Db::get_bot_name();
        $redis = Db::get_redis();

        //记录进入群的时间
        $redis->hSet($bot . 'chat:' . $this->chat_id . ':enter_chat_times', $this->new_id, time());
    }

    /**
     * 有人离开群的时候
     */
    public function msg_left_chat()
    {
        Common::echo_log("有人离开群");

        $bot = Db::get_bot_name();
        $redis = Db::get_redis();

        //记录离开群的时间
        $redis->hSet($bot . 'chat:' . $this->chat_id . ':left_chat_times', $this->level_id, time());

        //删除离开的用户的数据
        $redis->sRemove($bot . 'chat:' . $this->chat_id . ':users', $this->level_id);
        $redis->del($bot . 'msgs:' . $this->level_id . ':' . $this->chat_id);
        $redis->del($bot . 'day_msgs:' . date('Ymd') . $this->level_id . ':' . $this->chat_id);
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("执行 Stats run text=%s", $this->parms);

        if (in_array($this->common, array('/state', '/状态'))) {
            $user_id = empty($this->parms[0]) ? $this->from_id : $this->parms[0];
            $res_str = $this->get_user_stats($this->chat_id, $user_id);
        } elseif (isset($this->parms[0]) && $this->parms[0] == 'rebuild') {
            $res_str = $this->rebuild_day_msgs();
        } elseif (isset($this->parms[0]) && $this->parms[0] == 'd1') {
            $res_str = $this->data_d1();
        } else {
            $day_id = date('Ymd');
            $limit = self::DEFAULT_SHOW_LIMIT;

            if (!empty($this->parms[0])) {
                $day_id = $this->parms[0];
            }

            if (!empty($this->parms[1])) {
                $limit = $this->parms[1];
            } else {
                if (in_array($day_id, array('all', '*'))) {
                    $limit = $limit / 2;
                }
            }

            $res_str = $this->get_chat_stats($this->chat_id, $day_id, $limit);
        }

        //如果太长了
        if (mb_strlen($res_str) > 4096) {
            $res_str = mb_substr($res_str, 0, 4000);
        }

        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
