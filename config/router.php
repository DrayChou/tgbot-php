<?php

/**
 * 路由规则
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:20
 */
return array(
    /**
     * 帮助脚本的匹配规则
     */
    '#^/(help)$#iu'                        => 'help',
    '#^/(help)(@)(.*)$#iu'                 => 'help',
    '#^/(help) (.*)$#iu'                   => 'help',
    '#^/(help)(@)(.*) (.*)$#iu'            => 'help',

    /**
     * 图灵机器人的匹配规则
     */
    '#^/(bot)$#iu'                         => 'bot',
    '#^/(bot)(@)(.*)$#iu'                  => 'bot',
    '#^/(bot) (.*)$#iu'                    => 'bot',
    '#^/(bot)(@)(.*) (.*)$#iu'             => 'bot',

    /**
     * 谷歌搜索的匹配规则
     */
    '#^/(google)$#iu'                      => 'google',
    '#^/(google)(@)(.*)$#iu'               => 'google',
    '#^/(google) (.*)$#iu'                 => 'google',
    '#^/(google)(@)(.*) (.*)$#iu'          => 'google',

    /**
     * 谷歌图片搜索的匹配规则
     */
    '#^/(img)$#iu'                         => 'img',
    '#^/(img)(@)(.*)$#iu'                  => 'img',
    '#^/(img) (.*)$#iu'                    => 'img',
    '#^/(img)(@)(.*) (.*)$#iu'             => 'img',

    /**
     * 用户在线状态接口的匹配规则
     */
    '#^/(stats)$#iu'                       => 'stats',
    '#^/(stats)(@)(.*)$#iu'                => 'stats',
    '#^/(stats) (.*) ([-|\w]+)$#iu'        => 'stats',
    '#^/(stats)(@)(.*) (.*) ([-|\w]+)$#iu' => 'stats',
    '#^/(stats) (.*)$#iu'                  => 'stats',
    '#^/(stats)(@)(.*) (.*)$#iu'           => 'stats',
    '#^/(state)$#iu'                       => 'stats',
    '#^/(state)(@)(.*)$#iu'                => 'stats',
    '#^/(state) (.*)$#iu'                  => 'stats',
    '#^/(state)(@)(.*) (.*)$#iu'           => 'stats',
    '#^/(state) @(.*)$#iu'                 => 'stats',
    '#^/(state)(@)(.*) @(.*)$#iu'          => 'stats',

    /**
     * 机器人的输出什么命令
     */
    '#^/(echo) (.*)$#iu'                   => 'echo_',
    '#^/(echo)(@)(.*) (.*)$#iu'            => 'echo_',

    /**
     * 查询ID
     */
    '#^/(id)$#iu'                          => 'id',
    '#^/(id)(@)(.*)$#iu'                   => 'id',
);
