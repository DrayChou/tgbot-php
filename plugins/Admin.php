<?php

/**
 * @Author: dray
 * @Date:   2016-05-15 11:43:42
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-15 16:46:29
 */

class Admin extends Base
{
    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "/admin - Management Tools.",
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
            "/admin - Management Tools.",
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
            '/admin',
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("执行 Admin run");

        // 如果是 私聊
        if ($this->chat_id > 0) {
            Common::echo_log("Admin: 不是群聊，退出");
            return true;
        }

        //如果没有参数
        if (empty($this->text)) {
            Common::echo_log("Admin: 没有参数，退出");

            return true;
        }

        //检查参数
        $tmp = explode(' ', $this->text, 2);
        if (count($tmp) != 2) {
            Common::echo_log("Admin: 参数不足，退出");
            return true;
        }

        $bot = Db::get_bot_name();
        $redis = Db::get_redis();

        $config_key = $bot . "chat:{$this->chat_id}:config";
        $admins_key = $bot . "chat:{$this->chat_id}:admins";

        //检查权限
        $big_admins = Common::get_config('admins');
        $chat_admins = $redis->hGetAll($admins_key);
        $all_admins = array_merge($big_admins, array_keys($chat_admins));
        if (!in_array($this->from_id, $all_admins)) {
            Common::echo_log("Admin: 不是管理员，退出");
            return;
        }

        $do = $tmp[0];
        $value = $tmp[1];
        $res_str = '';

        Common::echo_log("Admin: do:{$do} $value:{$value}");

        switch ($do) {
            //添加管理员
            case 'add':{
                    //设置值
                    for ($set = strtok($value, ", \n\t"); $set !== false; $set = strtok(" \n\t")) {
                        Common::echo_log("Admin: 处理参数:{$set}");

                        if (empty($set)) {
                            continue;
                        }

                        //已经被添加过
                        if ($admin = $redis->hget($admins_key, $set)) {
                            $res_str .= "{$set} was added by {$admin['add_by_name']}" . PHP_EOL;
                            continue;
                        }

                        //群组里是否收录
                        if (!$redis->sIsMember($bot . 'chat:' . $this->chat_id . ':users', $set)) {
                            $res_str .= "{$set}: Group members are not included." . PHP_EOL;
                            continue;
                        }

                        //是否系统里有这个用户的信息
                        $user = $redis->hGet($bot . 'users:list', $set);
                        if (empty($user)) {
                            $res_str .= "{$set}: User are not included." . PHP_EOL;
                            continue;
                        }

                        $v = array(
                            'create_time' => time(),
                            'add_by_id' => $this->from_id,
                            'add_by_name' => $this->from_name,
                        );

                        if (false === $redis->hSet($admins_key, $set, json_encode($v))) {
                            $res_str .= "adminer add failed:{$set}" . PHP_EOL;
                            continue;
                        }

                        $res_str .= "adminer add success:{$set}" . PHP_EOL;

                        $set = strtok(" \n\t");
                    }

                    break;
                }
            //删除管理员
            case 'rm':{
                    //设置值
                    for ($set = strtok($value, ", \n\t"); $set !== false; $set = strtok(" \n\t")) {
                        Common::echo_log("Admin: 处理参数:{$set}");

                        if (empty($set)) {
                            continue;
                        }

                        //检查用户
                        if (!$admin = $redis->hget($admins_key, $set)) {
                            $res_str .= "{$set}: Invalid user" . PHP_EOL;
                            continue;
                        }
                        $admin = json_decode($admin, true);

                        //如果是 总管理员添加的，群管理员不能删除
                        if (in_array($admin['add_by_id'], $big_admins)) {
                            if (!(in_array($this->from_id, $big_admins))) {
                                $res_str .= "{$set}: can't remove, add by super admin: {$admin['add_by_name']}" . PHP_EOL;
                            }
                        }

                        if (!$redis->hDel($admins_key, $set)) {
                            $res_str .= "adminer rm failed:{$set}" . PHP_EOL;
                            continue;
                        }

                        $res_str .= "adminer rm success:{$set}" . PHP_EOL;

                        $set = strtok(" \n\t");
                    }

                    break;
                }
        }

        Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
