<?php

/**
 * 脚本调用执行类库
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:22
 */

require_once(LIB_PATH . 'db.php');

class Process
{

    static private $instance = array();

    /**
     * @param null $class_name
     * @return Base
     */
    static public function get_class($class_name = NULL) {
        if (!isset(self::$instance[$class_name])) {
            $class = ucfirst(strtolower($class_name));

            require_once(BASE_PATH . 'plugins' . DIRECTORY_SEPARATOR . 'Base.php');
            require_once(BASE_PATH . 'plugins' . DIRECTORY_SEPARATOR . $class . '.php');

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
    static function run($messages = NULL) {
        CFun::echo_log('处理消息列表: $messages=%s', print_r($messages, true));

        if (empty($messages)) {
            $limit          = 100;
            $last_update_id = Db::get_update_id();
            if ($last_update_id == 0) {
                $limit = 1;
            }

            // 抓到更新的信息
            $messages = Telegram::singleton()->get_updates(array(
                'offset' => $last_update_id + 1,
                'limit'  => $limit,
            ));
        }

        foreach ($messages as $message) {
            // 处理消息
            self::handler($message);
        }
    }

    /**
     * 执行抓取到的命令
     * @param $messages
     */
    static function handler($message) {
        //拿取路由规则
        $router = Db::get_router();

        if (is_string($message)) {
            $message = json_decode($message, true);
        }

        //如果是无效的消息的话，跳过
        if (empty($message) || empty($message['message'])) {
            CFun::echo_log('跳出，没有消息');

            return;
        }

        $last_update_id = Db::get_update_id();

        CFun::echo_log('最后一次处理的消息ID: $last_update_id=%s', $last_update_id);

        //之前已经处理过的信息，跳过
        if ($message['update_id'] <= $last_update_id) {
            CFun::echo_log('跳过，已经处理过的消息');

            return;
        }

        $msg            = $message['message'];
        $last_update_id = $message['update_id'];

        //更新 update_ID
        Db::set_update_id($last_update_id);

        //保存解析到的数据
        $text    = NULL;
        $parms   = NULL;
        $plugins = NULL;

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

        // 解析话语，抓到需要调用的机器人
        if (isset($msg['text'])) {
            //抓文字里的关键词，抓到是要请求什么插件
            foreach ($router as $reg => $class) {
                $is_match = preg_match($reg, $msg['text'], $m);

                if ($is_match) {
                    CFun::echo_log('正则匹配结果: $reg=%s $text=%s $m=%s', $reg, $msg['text'], $m);
                    CFun::echo_log('正则匹配到的插件: $class=%s', $class);

                    $text  = trim($m[3]);
                    $parms = array($m[1]);

                    $tmp = explode(' ', $text);
                    $tmp = array_filter($tmp);

                    $parms = array_merge($parms, $tmp);

                    //加载消息类
                    $plugins = self::get_class($class);
                    break;
                }
            }
        }

        //执行需要调用的函数
        self::loop_with($run_fun, $msg, $text, $parms);

        //执行消息的运行命令
        if (!empty($plugins)) {
            $plugins->set_msg($msg, $text, $parms);
            $plugins->run();
        }
    }

    /**
     * 执行对应的命令
     * @param $comm
     */
    static function loop_with($fun_arr, $msg, $text = NULL, $parms = NULL) {
        $router  = CFun::get_router();
        $plugins = array_flip($router);
        foreach ($plugins as $class_name => $tmp) {
            $class = self::get_class($class_name);
            $class->set_msg($msg, $text, $parms);

            foreach ($fun_arr as $fun) {
                $class->$fun();
            }
        }
    }

}
