<?php

/**
 * 基础类库
 * User: dray
 * Date: 15/7/10
 * Time: 下午4:23
 */
class Base
{
    const WAIT_FOR_QUESTION = 60;

    public $msg; //消息整体

    public $msg_id; //消息ID

    public $chat_id; //聊天对象信息
    public $chat_name;

    public $from;
    public $from_id; //消息发送者
    public $from_name;

    public $new;
    public $new_id; //新的朋友
    public $new_name; //新的朋友

    public $level;
    public $level_id; //离开的朋友
    public $level_name; //离开的朋友

    public $text; //命令后面的内容
    public $parms; //空格分隔的命令
    public $parm; //收到的文本内容
    public $common; //命令

    /**
     * @param $msg
     * @throws Exception
     */
    public function set_msg($msg, $text = null, $parms = null, $common = null)
    {
        if (empty($msg['message_id'])) {
            throw new Exception('error message');
        }

        $this->msg = $msg;
        $this->msg_id = $msg['message_id'];
        $this->text = $text;
        $this->parms = $parms;
        $this->common = $common;

        $this->from = $msg['from'];
        $this->from_id = $msg['from']['id'];
        $this->from_name = self::getUserName($msg['from']);

        if (isset($msg['text'])) {
            $this->parm = $msg['text'];
        }

        // 群组ID
        $this->chat_id = $msg['chat']['id'];
        if ($this->chat_id < 0) {
            //群组聊天
            $this->chat_name = $msg['chat']['title'];
        } else {
            //个人聊天
            $this->chat_name = $msg['chat']['username'];
        }

        // 有人进来了
        if (isset($msg['new_chat_participant'])) {
            $this->new = $msg['new_chat_participant'];
            $this->new_id = $msg['new_chat_participant']['id'];
            $this->new_name = self::getUserName($msg['new_chat_participant']);
        }

        // 有人走了
        if (isset($msg['left_chat_participant'])) {
            $this->level = $msg['left_chat_participant'];
            $this->level_id = $msg['left_chat_participant']['id'];
            $this->level_name = self::getUserName($msg['left_chat_participant']);
        }
    }

    /**
     * 当有人进入群的时候
     */
    public function msg_enter_chat()
    {
        Common::echo_log("有人进入群");
    }

    /**
     * 有人离开群的时候
     */
    public function msg_left_chat()
    {
        Common::echo_log("有人离开群");
    }

    /**
     * 有人回复我
     */
    public function msg_reply_me()
    {
        Common::echo_log("有人回复我");
    }

    /**
     * 不管什么情况都会执行的函数
     */
    public function pre_process()
    {
        $class = get_called_class();
        if (!$this->is_has_reply($class)) {
            // Common::echo_log($class . " pre_process 没有需要处理的 跳过");

            return;
        }

        Common::echo_log($class . " pre_process 抓到需要处理的回复 parms=%s", $this->msg['text']);

        $key = 'need_reply:' . $class . ':' . $this->chat_id . ':' . $this->from_id;
        Db::set($key, null, -1);

        $this->set_msg($this->msg, $this->msg['text']);
        $this->run();
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     */
    public function run()
    {
        Common::echo_log("做点什么");
    }

    /**
     * 命令说明
     * Command Description
     * @return string
     */
    public static function desc()
    {
        return array(
            "插件说明，一行，用在 help 中",
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
            "插件说明，数组，用在功能调用的说明上。",
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
        );
    }

    /**
     * 设置回掉回复消息
     * @param $res_str
     * @throws Exception
     */
    protected function set_reply($res_str = null)
    {
        $class = get_called_class();
        Common::echo_log($class . " 设置回掉信息: chat_id=%s from_id=%s", $this->chat_id, $this->from_id);

        if (empty($res_str)) {
            $res_str = '';
            $res_str .= 'What are you looking for? E.g.:' . PHP_EOL;
            $res_str .= 'happy dog' . PHP_EOL;
            $res_str .= PHP_EOL;
            $res_str .= "You can also use this format to get results faster: " . PHP_EOL;
            $res_str .= join(PHP_EOL, $class::desc());
        }

        //回复消息
        $msg = Telegram::singleton()->send_message(array(
            'chat_id' => $this->chat_id,
            'text' => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));

        //记录一个状态，下次检测到这个用户在这个群组里说过话之后就失效掉
        $key = 'need_reply:' . $class . ':' . $this->chat_id . ':' . $this->from_id;
        Db::set($key, 1, $class::WAIT_FOR_QUESTION);
    }

    /**
     * 检查是否有需要回掉的消息
     * @return bool
     */
    protected function is_has_reply($class = '*')
    {
        $key = 'need_reply:' . $class . ':' . $this->chat_id . ':' . $this->from_id;
        $val = Db::get($key);

        Common::echo_log("Base: is_has_reply key=%s val=%s", $key, $val);

        return $val;
    }

    /**
     * 得到显示出来的名称
     * @param $data
     * @return string
     */
    protected static function getUserName($data)
    {
        $name = '';
        if (isset($data['first_name'])) {
            $name = $data['first_name'];
            if (isset($data['last_name'])) {
                $name .= ('_' . $data['last_name']);
            }
        } else {
            $name = $data['username'];
        }

        return $name;
    }
}
