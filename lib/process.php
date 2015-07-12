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

        if (is_string($messages)) {
            $messages = json_decode($messages, true);
        }

        if (empty($messages)) {
            CommonFunction::echo_log('跳出，没有消息');
            return;
        }

        $last_update_id = Db::get_update_id();
        foreach ($messages as $message) {
            CommonFunction::echo_log('最后一次处理的消息ID: $last_update_id=%s', $last_update_id);

            //如果是无效的消息的话，跳过
            if (!isset($message['message'])) {
                CommonFunction::echo_log('跳过，无效的消息');
                continue;
            }

            //之前已经处理过的信息，跳过
            if ($message['update_id'] <= $last_update_id) {
                CommonFunction::echo_log('跳过，已经处理过的消息');
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

                        CommonFunction::echo_log('正则匹配结果: $m=%s', $m);

                        $text = NULL;
                        if (isset($m[2])) {
                            if ($m[2] == '@') {
                                if (strtolower($m[3]) != strtolower($config['bot_name'])) {
                                    continue;
                                }

                                $text = $m[4];
                            } else {
                                $text = $m[2];
                            }
                        }

                        $plugins = self::get_class($class);
                        $plugins->set_msg($msg, $text);

                        CommonFunction::echo_log('正则匹配到的插件: $plugins=%s', $plugins);
                        //执行消息的运行命令
                        $plugins->run();

                        break;
                    }
                }
            }

            //更新 update_ID
            Db::set_update_id($last_update_id);
        }
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
