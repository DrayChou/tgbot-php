<?php
/**
 * 脚本调用执行类库
 * process
 * @Author: dray
 * @Date:   2015-07-10 15:22:59
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-05 17:16:09
 */

class Process
{
    private static $instance = array();

    /**
     * @param null $class_name
     * @return Base
     */
    public static function get_class($class_name = null)
    {
        if (!isset(self::$instance[$class_name])) {
            $class = ucfirst(strtolower($class_name));

            if (!class_exists('Base')) {
                require_once PLUGIN_PATH . 'Base.php';
            }

            if (!class_exists($class)) {
                require_once PLUGIN_PATH . $class . '.php';
            }

            self::$instance[$class_name] = new $class($class_name);
        }

        return self::$instance[$class_name];
    }

    /**
     * 调用执行的函数
     * 如果有消息传进来，那么就用传进来的，没有就自己去抓
     * @param $message
     * @throws Exception
     */
    public static function run($messages = null)
    {
        Common::echo_log('处理消息列表: $messages=%s', print_r($messages, true));

        if (empty($messages)) {
            $limit = 100;
            $last_update_id = Db::get_update_id();
            if ($last_update_id == 0) {
                $limit = 1;
            }

            // 抓到更新的信息
            $messages = Telegram::singleton()->get_updates(array(
                'offset' => $last_update_id + 1,
                'limit' => $limit,
            ));
        }

        if (empty($messages) || !is_array($messages)) {
            Common::echo_log('无效的消息列表: $messages=%s', print_r($messages, true));

            return;
        }

        foreach ($messages as $msg) {
            // 处理消息
            self::handler($msg);
        }
    }

    /**
     * 执行抓取到的命令
     * @param $messages
     */
    public static function handler($message)
    {
        //拿取路由规则
        $router = Db::get_router();
        $bot_info = Db::get_bot_info();

        if (is_string($message)) {
            $message = json_decode($message, true);
        }

        //如果是无效的消息的话，跳过
        if (empty($message) || empty($message['message'])) {
            Common::echo_log('跳出，没有消息');

            return;
        }

        $last_update_id = Db::get_update_id();

        Common::echo_log('最后一次处理的消息ID: $last_update_id=%s', $last_update_id);

        //之前已经处理过的信息，跳过
        if ($message['update_id'] <= $last_update_id) {
            Common::echo_log('跳过，已经处理过的消息');

            return;
        }

        $msg = $message['message'];
        $last_update_id = $message['update_id'];

        //更新 update_ID
        Db::set_update_id($last_update_id);

        //保存解析到的数据
        $common = null;
        $text = null;
        $parms = null;
        $plugins = null;

        //不管什么情况每次都要执行一次的函数
        $run_fun = array(
            'pre_process',
        );

        //如果有新人的话
        if (isset($msg['new_chat_participant'])) {
            $run_fun[] = 'msg_enter_chat';
        }

        //如果有人离开的话
        if (isset($msg['left_chat_participant'])) {
            $run_fun[] = 'msg_left_chat';
        }

        //如果有人回复机器人的话
        if (isset($msg['reply_to_message'])) {
            if ($msg['reply_to_message']['from']['id'] == $bot_info['id']) {
                $run_fun[] = 'msg_reply_me';
            }
        }

        // 解析话语，抓到需要调用的机器人
        if (isset($msg['text'])) {
            //抓文字里的关键词，抓到是要请求什么插件
            foreach ($router as $reg => $class) {
                $tmp = explode(' ', $msg['text']);
                $tmp = array_filter($tmp);

                //如果命令调用名称一致
                if (
                    strtolower($reg . '@' . $bot_info['username']) == strtolower($tmp[0]) ||
                    strtolower($reg) == strtolower($tmp[0])
                ) {
                    $common = array_shift($tmp);

                    $parms = $tmp;
                    $text = join(' ', $tmp);

                    Common::echo_log("命中 reg=%s common=%s class=%s text=%s parms=%s", $reg, $common, $class, $text, print_r($parms, true));

                    //加载消息类
                    $plugins = self::get_class($class);
                    break;
                }
            }
        }

        $bot_open_plugins = Common::get_config('plugins');
        if (isset($bot_info['username']) && isset($bot_open_plugins[strtolower($bot_info['username'])])) {
            if (!in_array(strtolower(get_class($plugins)), $bot_open_plugins[strtolower($bot_info['username'])])) {
                return;
            }
        }

        //执行需要调用的函数
        self::loop_with($run_fun, $msg, $text, $parms, $common);

        //执行消息的运行命令
        if (!empty($plugins)) {
            $plugins->set_msg($msg, $text, $parms, $common);
            $plugins->run();
        }
    }

    /**
     * 执行对应的命令
     * @param $comm
     */
    public static function loop_with($fun_arr, $msg, $text = null, $parms = null, $common = null)
    {
        $router = Common::get_router();
        $plugins = array_flip($router);
        foreach ($plugins as $class_name => $tmp) {
            $class = self::get_class($class_name);
            $class->set_msg($msg, $text, $parms, $common);

            foreach ($fun_arr as $fun) {
                $class->$fun();
            }
        }
    }
}
