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

    static function desc() {
        return "/stats - Plugin to update user stats.  ";
    }

    static function usage() {
        return array(
            "/stats - Returns a list of Username [telegram_id]: msg_num only top." . self::DEFAULT_SHOW_LIMIT,
            "/stats 20150528 - Returns this day stats",
            "/stats all: Returns All days stats.",
            "/stats 20150528 " . self::DEFAULT_SHOW_LIMIT . ": Returns a list only top " . self::DEFAULT_SHOW_LIMIT,
            "/state user_id: Returns this user All days stats",
        );
    }

    /**
     * 重建群每日发言数数据
     */
    private function build_stats_data($search_chat_id, $search_day_id) {
        $bot   = Db::get_bot_name();
        $redis = Db::get_redis();

        $count     = array();
        $days_list = $redis->keys($bot . 'day_msgs:' . $search_day_id . ':*:' . $search_chat_id);
        foreach ($days_list as $k => $v) {
            $keys    = explode(':', $v);
            $day_id  = $keys[2];
            $user_id = $keys[3];
            $chat_id = $keys[4];

            if (!isset($count[$chat_id])) {
                $count[$chat_id][$day_id] = 1;
            }

            if (!isset($count[$chat_id][$day_id])) {
                $count[$chat_id][$day_id] = 1;
            }

            $count[$chat_id][$day_id] += (int)$redis->get($v);
        }

        foreach ($count as $c => $days) {

            $mxd = NULL;
            $mxm = 0;
            $mid = NULL;
            $mim = PHP_INT_MAX;

            foreach ($days as $d => $n) {
                if ($n > $mxm) {
                    $mxd = $d;
                    $mxm = $n;
                }

                if ($n < $mim) {
                    $mid = $d;
                    $mim = $n;
                }

                $redis->hSet($bot . 'stats:chat:' . $c, $d, $n);
            }

            if ($search_day_id == '*') {
                $redis->hSet($bot . 'stats:chat:' . $c, 'max_day', $mxd);
                $redis->hSet($bot . 'stats:chat:' . $c, 'min_day', $mid);
            } else {
                $max_day = (int)$redis->hGet($bot . 'stats:chat:' . $c, 'max_day');
                $min_day = (int)$redis->hGet($bot . 'stats:chat:' . $c, 'min_day');

                $max_msgs = (int)$redis->hGet($bot . 'stats:chat:' . $c, $max_day);
                $min_msgs = (int)$redis->hGet($bot . 'stats:chat:' . $c, $min_day);

                if ($mxm > $max_msgs) {
                    $redis->hSet($bot . 'stats:chat:' . $c, 'max_day', $max_day);
                }

                if ($mim < $min_msgs) {
                    $redis->hSet($bot . 'stats:chat:' . $c, 'min_day', $min_day);
                }
            }
        }
    }

    /**
     * 得到群最热闹的一天和最不热闹的一天的数据
     * @param $chat_id
     * @return array
     */
    private function get_chat_mx($chat_id) {
        $bot   = Db::get_bot_name();
        $redis = Db::get_redis();

        $max_day = (int)$redis->hGet($bot . 'stats:chat:' . $chat_id, 'max_day');
        $min_day = (int)$redis->hGet($bot . 'stats:chat:' . $chat_id, 'min_day');

        if ($max_day == 0 || $min_day == 0) {
            $this->build_stats_data($chat_id, '*');

            $max_day = (int)$redis->hGet($bot . 'stats:chat:' . $chat_id, 'max_day');
            $min_day = (int)$redis->hGet($bot . 'stats:chat:' . $chat_id, 'min_day');
        }

        $max_msgs = (int)$redis->hGet($bot . 'stats:chat:' . $chat_id, $max_day);
        $min_msgs = (int)$redis->hGet($bot . 'stats:chat:' . $chat_id, $min_day);

        return array(
            'mxd' => $max_day,
            'mxm' => $max_msgs,
            'mid' => $min_day,
            'mim' => $min_msgs,
        );
    }

    /**
     * 得到这个群组中所有的用户和聊天记录信息
     * @param $chat_id
     * @param null $day_id
     * @return array
     */
    private function get_chat_users($chat_id, $day_id = NULL) {
        $bot   = Db::get_bot_name();
        $redis = Db::get_redis();

        //预处理
        if(empty($day_id)){
            $day_id = date('Ymd');
        }elseif(!is_numeric($day_id)){
            if(in_array(strtolower($day_id),array('a', 'all', '*'))){
                $day_id = '*';
            }elseif (in_array(strtolower($day_id),array('t', 'tdy', 'today'))) {
                $day_id = date('Ymd');
            }
        }

        $users_info = array();
        $users      = $redis->sMembers($bot . 'chat:' . $chat_id . ':users');
        foreach ($users as $k => $v) {
            if ($day_id == '*') {
                $msgs = (int)$redis->get($bot . 'msgs:' . $v . ':' . $chat_id);
            } else {
                $msgs = (int)$redis->get($bot . 'day_msgs:' . $day_id . ':' . $v . ':' . $chat_id);
            }

            $users_info[] = array(
                'id'   => (int)$v,
                'name' => $redis->hGet($bot . 'users:ids', $v),
                'msgs' => $msgs,
            );
        }

        return $users_info;
    }

    /**
     * 得到群的聊天状况
     * @param $chat_id
     * @param $day_id
     * @param $limit
     * @return string
     */
    private function get_chat_stats($chat_id, $day_id, $limit) {
        $uses_info = $this->get_chat_users($chat_id, $day_id);

        $order_by = SORT_DESC;
        if ($limit < 0) {
            $order_by = SORT_ASC;
            $limit    = -1 * $limit;
        }

        $sort_by = array();
        foreach ($uses_info as $user) {
            $sort_by[] = $user['msgs'];
        }

        array_multisort($sort_by, $order_by, SORT_NUMERIC, $uses_info);

        $text = strtoupper($day_id) . ' TOP ' . $limit;

        $top_sum = 0;
        $all_sum = 0;
        foreach ($uses_info as $i => $user) {
            $all_sum += $user['msgs'];

            if ($i < $limit) {
                $top_sum += $user['msgs'];
                $text .= (PHP_EOL . $user['name'] . ' => ' . $user['msgs']);
            }
        }

        $chat_max = $this->get_chat_mx($chat_id);

        $text .= (PHP_EOL . ' top sum:' . $top_sum);
        $text .= (PHP_EOL . ' all sum:' . $all_sum);
        $text .= (PHP_EOL . ' top/all:' . intval($top_sum / ($all_sum == 0 ? 1 : 0) * 100) . '%');
        $text .= (PHP_EOL . ' max day:' . ($chat_max['mxd'] . ' => ' . $chat_max['mxm']));
        $text .= (PHP_EOL . ' min day:' . ($chat_max['mid'] . ' => ' . $chat_max['mim']));

        return $text;
    }

    private function get_user_stats($chat_id, $user_id) {
        $bot   = Db::get_bot_name();
        $redis = Db::get_redis();

        if ($user_id == 'me') {
            $user_id = $this->from_id;
        }

        if (!is_numeric($user_id)) {
            if ($tmp_id = $redis->hGet($bot . 'users:usernames', $user_id)) {
                $user_id = $tmp_id;
            }
        }

        $all_sum    = 0;
        $user_sum   = 0;
        $users_info = $this->get_chat_users($chat_id);
        foreach ($users_info as $user) {
            $all_sum += $user['msgs'];

            if ($user['id'] == $user_id) {
                $user_sum = (int)$user['msgs'];
            }
        }

        $day_all_sum    = 0;
        $day_user_sum   = 0;
        $day_users_info = $this->get_chat_users($chat_id, date('Ymd'));
        foreach ($day_users_info as $user) {
            $day_all_sum += $user['msgs'];

            if ($user['id'] == $user_id) {
                $day_user_sum = (int)$user['msgs'];
            }
        }

        $show_name = $redis->hGet($bot . 'users:ids', $user_id);

        $text = '';
        $text .= ($show_name . ' stats:' . PHP_EOL);
        $text .= ('stats count:' . $user_sum . PHP_EOL);
        $text .= ('all user sum:' . $all_sum . PHP_EOL);
        $text .= ('user/all:' . intval($user_sum / ($all_sum == 0 ? 1 : 0) * 100) . '%' . PHP_EOL);
        $text .= ('user today count:' . $day_user_sum . PHP_EOL);
        $text .= ('all user today sum:' . $day_all_sum . PHP_EOL);
        $text .= ('user/all:' . intval($day_user_sum / ($day_all_sum == 0 ? 1 : 0) * 100) . '%' . PHP_EOL);

        return $text;
    }

    /**
     * 不管什么情况都会执行的函数
     */
    public function pre_process() {
        CFun::echo_log("统计数据 开始");

        if (empty($this->parm)) {
            CFun::echo_log("统计数据 内容为空，跳过");

            return;
        }

        //如果不是群组聊天的话，跳过
        if ($this->chat_id >= 0) {
            CFun::echo_log("统计数据 不是群组聊天，跳过");

            return;
        }

        //如果是本机器人说的话，忽略不计
        $bot_info = Telegram::singleton()->get_me();
        if ($this->from_id == $bot_info['id']) {
            CFun::echo_log("统计数据 机器人自己发出的，跳过");

            return;
        }

        CFun::echo_log("统计数据 更新数据");

        $bot   = Db::get_bot_name();
        $redis = Db::get_redis();

        //记录发言人的信息
        $redis->hSet($bot . 'users:ids', $this->from_id, $this->from_name);
        $redis->hSet($bot . 'users:usernames', $this->from_username, $this->from_id);

        // 添加用户ID到群组中
        $redis->sadd($bot . 'chat:' . $this->chat_id . ':users', $this->from_id);

        // 记录用户在这个群组中的聊天数
        $redis->incr($bot . 'msgs:' . $this->from_id . ':' . $this->chat_id);

        // 记录用户在这个群组中的聊天数,这天
        $redis->incr($bot . 'day_msgs:' . date('Ymd') . ':' . $this->from_id . ':' . $this->chat_id);

        //刷新每天这个群的最大最小发言数
        $this->build_stats_data($this->chat_id, date('Ymd'));

        CFun::echo_log("统计数据 数据更新完毕");
    }

    /**
     * 有人离开群的时候
     */
    public function msg_left_chat() {
        CFun::echo_log("有人离开群");

        $bot   = Db::get_bot_name();
        $redis = Db::get_redis();

        //删除离开的用户的数据
//        $redis->hDel($bot . 'users:ids', $this->level_id);
//        $redis->hDel($bot . 'users:usernames', $this->level_username);
        $redis->sRemove($bot . 'chat:' . $this->chat_id . ':users', $this->level_id);
        $redis->del($bot . 'msgs:' . $this->level_id . ':' . $this->chat_id);
        $redis->del($bot . 'day_msgs:' . date('Ymd') . $this->level_id . ':' . $this->chat_id);
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run() {
        CFun::echo_log("执行 Stats run text=%s", $this->parms);

        if ($this->parms[0] == 'state') {

            $user_id = empty($this->parms[1]) ? $this->from_id : $this->parms[1];
            $res_str = $this->get_user_stats($this->chat_id, $user_id);

        } else {

            $day_id = date('Ymd');
            $limit  = self::DEFAULT_SHOW_LIMIT;

            if (!empty($this->parms[1])) {
                $day_id = $this->parms[1];
            }

            if (!empty($this->parms[2])) {
                $limit = $this->parms[2];
            } else {
                if (in_array($day_id, array('all', '*'))) {
                    $limit = $limit / 2;
                }
            }

            $res_str = $this->get_chat_stats($this->chat_id, $day_id, $limit);

        }

        $msg = Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));

        CFun::echo_log("发送信息: msg=%s", $msg);
    }
}
