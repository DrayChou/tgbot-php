<?php

/**
 * 脚本调用执行类库
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:22
 */
class Process {

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
     * 执行抓取到的命令
     * @param $messages
     */
    static function run($messages) {
        $config = CommonFunction::get_config();
        $router = CommonFunction::get_router();
        CommonFunction::echo_log('加载路由规则: router=%s', $router);

        if (is_string($messages)) {
            $messages = json_decode($messages, true);
        }

        CommonFunction::echo_log('得到的消息列表: $messages=%s', $messages);
        if (empty($messages)) {
            return;
        }

        $last_update_id = Db::get_update_id();

        foreach ($messages as $message) {
            //如果是无效的消息的话，跳过
            if (!isset($message['message'])) {
                continue;
            }

            //之前已经处理过的信息，跳过
            if ($message['update_id'] <= $last_update_id) {
                continue;
            }

            $msg            = $message['message'];
            $last_update_id = $message['update_id'];

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

            //如果有人转发消息
            if (isset($msg['forward_from'])) {
                $run_fun[] = 'msg_forward';
            }

            //如果有人发图片
            if (isset($msg['new_chat_photo'])) {
                $run_fun[] = 'msg_photo';
            }

            //执行需要调用的函数
            self::run_with($run_fun, $msg);

            if (isset($msg['text'])) {
                $plugins = NULL;
                //抓文字里的关键词，抓到是要请求什么插件
                $text    = $msg['text'];
                foreach ($router as $reg => $class) {
                    if (preg_match($reg, $text, $m)) {

                        CommonFunction::echo_log('正则匹配结果: $messages=%s', $messages);

                        if ($m[2] == '@') {
                            if (strtolower($m[3] != strtolower($config['bot_name']))) {
                                continue;
                            }
                        }

                        $plugins = self::get_class($m[1]);
                        $plugins->set_msg($msg);

                        break;
                    }
                }

                //如果没有抓到要调用的插件，那么忽略掉
                if (empty($plugins)) {
                    continue;
                }

                //执行消息的运行命令
                $plugins->run();
            }
        }

        //更新 update_ID
        RedDbet_update_id($last_update_id);
    }

    /**
     * 得到说明信息
     * @return string
     */
    static function get_helps() {
        $helps   = array();
        $router  = CommonFunction::get_router();
        $plugins = array_flip($router);
        foreach ($plugins as $class_name => $tmp) {
            $class = self::get_class($class_name);
            $desc  = $class::desc();
            if (!is_array($desc)) {
                $desc = array($desc);
            }

            $helps = array_merge($helps, $desc);
        }

        return implode(PHP_EOL, $helps);
    }

    /**
     * 得到插件的详细使用说明信息
     * @param $class
     * @return mixed
     */
    static function get_usage($class) {
        $class = self::get_class($class);

        return $class::usage();
    }

    /**
     * 执行对应的命令
     * @param $comm
     */
    static function run_with($fun_arr, $msg) {
        $router  = CommonFunction::get_router();
        $plugins = array_flip($router);
        foreach ($plugins as $class_name => $tmp) {
            $class = self::get_class($class_name);
            $class->set_msg($msg);

            foreach ($fun_arr as $fun) {
                $class->$fun();
            }
        }
    }

}
