<?php

/**
 * 脚本调用执行类库
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:22
 */
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
    static function run($message = NULL) {
        // 回收运行结束的子进程
        $res = swoole_process::wait(true);
        CFun::echo_log('回收子进程 $res=%s', $res);

        if (empty($message)) {
            // 抓到更新的信息
            $message = Telegram::singleton()->post('getUpdates', array(
                'offset' => Db::get_update_id() + 1,
            ));
        }

        // 开启处理进程
        $process = new swoole_process(function ($process) {
            //接收数据
            $message = $process->read();
            self::handler($message);

            //结束进程
            $process->exit();
        });

        // 传入数据
        $process->write(json_encode($message));
        $pid = $process->start();
        CFun::echo_log('开启子进程 id=%s', $pid);
    }

    /**
     * 执行抓取到的命令
     * @param $messages
     */
    static function handler($messages) {
        $bot_info = Telegram::singleton()->get_me();
        $router   = CFun::get_router();

        if (is_string($messages)) {
            $messages = json_decode($messages, true);
        }

        if (empty($messages)) {
            CFun::echo_log('跳出，没有消息');

            return;
        }

        $last_update_id = Db::get_update_id();
        foreach ($messages as $message) {
            CFun::echo_log('最后一次处理的消息ID: $last_update_id=%s', $last_update_id);

            //如果是无效的消息的话，跳过
            if (!isset($message['message'])) {
                CFun::echo_log('跳过，无效的消息');
                continue;
            }

            //之前已经处理过的信息，跳过
            if ($message['update_id'] <= $last_update_id) {
                CFun::echo_log('跳过，已经处理过的消息');
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
            self::loop_with($run_fun, $msg);

            // 运行匹配到的规则
            if (isset($msg['text'])) {
                $plugins = NULL;
                //抓文字里的关键词，抓到是要请求什么插件
                $text = $msg['text'];
                foreach ($router as $reg => $class) {
                    if (preg_match($reg, $text, $m)) {

                        CFun::echo_log('正则匹配结果: $m=%s', $m);

                        $text = NULL;
                        $regs = array($m[1]);
                        if (isset($m[2])) {
                            $min_i = PHP_INT_MAX;

                            if ($m[2] == '@') {
                                if (strtolower($m[3]) != strtolower($bot_info['username'])) {
                                    continue;
                                }

                                if (isset($m[4])) {
                                    $text   = trim($m[4]);
                                    $regs[] = trim($m[4]);
                                    $min_i  = 5;
                                }
                            } else {
                                if (isset($m[2])) {
                                    $text   = trim($m[2]);
                                    $regs[] = trim($m[2]);
                                    $min_i  = 3;
                                }
                            }

                            if (count($m) > $min_i) {
                                for ($i = $min_i; $i < count($m); $i++) {
                                    $text .= (' ' . trim($m[$i]));
                                    $regs[] = trim($m[4]);
                                }
                            }
                        }

                        $plugins = self::get_class($class);
                        $plugins->set_msg($msg, $text, $regs);

                        CFun::echo_log('正则匹配到的插件: $plugins=%s', $plugins);
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
     * 执行对应的命令
     * @param $comm
     */
    static function loop_with($fun_arr, $msg) {
        $router  = CFun::get_router();
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
