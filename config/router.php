<?php

/**
 * 路由规则
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:20
 */
return array(
    //帮助脚本的匹配规则
    '#^/(help)(@%%bot_name%%| |)(.*)$#iu'      => 'help',

    //谷歌搜索的匹配规则
    '#^/(google)(@%%bot_name%%| |)(.*)$#iu'    => 'google',

    //谷歌图片搜索的匹配规则
    '#^/(img)(@%%bot_name%%| |)(.*)$#iu'       => 'googleimg',

    //用户在线状态接口的匹配规则
    '#^/(stats)(@%%bot_name%%| |)(.*)$#iu'     => 'stats',
    '#^/(state)(@%%bot_name%%| |)(.*)$#iu'     => 'stats',

    //机器人的输出什么命令
    '#^/(echo)(@%%bot_name%%| |)(.*)$#iu'      => 'echo_',

    //查询ID
    '#^/(id)(@%%bot_name%%| |)(.*)$#iu'        => 'id',

    //计算数学
    '#^/(calc)(@%%bot_name%%| |)(.*)$#iu'      => 'calculate',

    //搜索百度盘
    '#^/(bp)(@%%bot_name%%| |)(.*)$#iu'        => 'googlebaidupan',

    //图灵机器人的匹配规则
    '#^/(tuling123)(@%%bot_name%%| |)(.*)$#iu' => 'tuling123',

    //cleverbot 的聊天机器人的匹配规则
    '#^/(cleverbot)(@%%bot_name%%| |)(.*)$#iu' => 'cleverbot',

    //机器人
    '#^/(bot)(@%%bot_name%%| |)(.*)$#iu'       => 'bot',

    '#^/(boobs)(@%%bot_name%%| |)(.*)$#iu'     => 'boobs',
    '#^/(butts)(@%%bot_name%%| |)(.*)$#iu'     => 'butts',
    '#^/(dogify)(@%%bot_name%%| |)(.*)$#iu'    => 'dogify',
    '#^/(zhihu)(@%%bot_name%%| |)(.*)$#iu'     => 'zhihu',
    '#^/(weather)(@%%bot_name%%| |)(.*)$#iu'   => 'weather',

    '#^/(wiki)(@%%bot_name%%| |)(.*)$#iu'      => 'wiki',
    '#^/(github)(@%%bot_name%%| |)(.*)$#iu'    => 'github',
);
