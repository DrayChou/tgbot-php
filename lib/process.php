<?php

/**
 * 脚本调用执行类库
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:22
 */
class Process
{

    /**
     * 执行抓取到的命令
     * @param $messages
     */
    static function run($messages) {
        $router = require(BASE_PATH . 'config' . DIRECTORY_SEPARATOR . 'router.php');
        var_dump($router);

        if (is_string($messages)) {
            $messages = json_decode($messages, true);
        }

        var_dump($messages);
        if (empty($messages)) {
            return;
        }

        foreach ($messages as $message) {
            //如果有新人的话
            if (!isset($message['message'])) {
                continue;
            }

            $msg = $message['message'];

            //不管什么情况每次都要执行一次的函数
            self::runWith('pre_process');

            //如果有新人的话
            if (isset($msg['new_chat_participant'])) {
                self::runWith('msg_enter_chat');
            }

            //如果有人离开的话
            if (isset($msg['left_chat_participant'])) {
                self::runWith('msg_left_chat');
            }

            //如果有人转发消息
            if (isset($msg['forward_from'])) {
                self::runWith('msg_forward');
            }

            //如果有人发图片
            if (isset($msg['new_chat_photo'])) {
                self::runWith('msg_photo');
            }

            if (isset($msg['text'])) {
                $plugins = NULL;
                //抓文字里的关键词，抓到是要请求什么插件
                $text = $msg['text'];
                foreach ($router as $reg => $class) {
                    if (preg_match($reg, $text, $m)) {
                        var_dump($m);
                        $class_name = ucfirst(strtolower($m[1]));

                        require_once(BASE_PATH . 'plugins' . DIRECTORY_SEPARATOR . 'Base.php');
                        require_once(BASE_PATH . 'plugins' . DIRECTORY_SEPARATOR . $class_name . '.php');

                        $plugins = new $class_name($msg);
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
    }

    /**
     * 得到说明信息
     * @return string
     */
    static function gethelp() {
        $helps = array();

        $router  = require(BASE_PATH . 'config' . DIRECTORY_SEPARATOR . 'router.php');
        $plugins = array_flip($router);
        foreach ($plugins as $class_name => $tmp) {
            $class = ucfirst(strtolower($class_name));

            require_once(BASE_PATH . 'plugins' . DIRECTORY_SEPARATOR . 'Base.php');
            require_once(BASE_PATH . 'plugins' . DIRECTORY_SEPARATOR . $class . '.php');

            $desc = $class::desc();
            if (!is_array($desc)) {
                $desc = array($desc);
            }

            $helps = array_merge($helps, $desc);
        }

        return implode('\n', $helps);
    }

    /**
     * 得到插件的详细使用说明信息
     * @param $class
     * @return mixed
     */
    static function getUsage($class) {
        $class = ucfirst(strtolower($class));

        require_once(BASE_PATH . 'plugins' . DIRECTORY_SEPARATOR . 'Base.php');
        require_once(BASE_PATH . 'plugins' . DIRECTORY_SEPARATOR . $class . '.php');

        return $class::usage();
    }

    /**
     * 执行对应的命令
     * @param $comm
     */
    static function runWith($comm) {
        $router  = require(BASE_PATH . 'config' . DIRECTORY_SEPARATOR . 'router.php');
        $plugins = array_flip($router);
        foreach ($plugins as $class_name => $tmp) {
            $class = ucfirst(strtolower($class_name));

            require_once(BASE_PATH . 'plugins' . DIRECTORY_SEPARATOR . 'Base.php');
            require_once(BASE_PATH . 'plugins' . DIRECTORY_SEPARATOR . $class . '.php');

            $class::$comm();
        }
    }
}

