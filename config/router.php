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
    '#^/(help)$#i'            => 'help',
    '#^/(help) (.*)#i'        => 'help',
    '#^/(help)(@)(.*)$#i'     => 'help',
    '#^/(help)(@)(.*) (.*)#i' => 'help',
    /**
     * 图灵机器人的匹配规则
     */
    '#^/(bot)$#i'            => 'tuling',
    '#^/(bot) (.*)#i'        => 'tuling',
    '#^/(bot)(@)(.*)$#i'     => 'tuling',
    '#^/(bot)(@)(.*) (.*)#i' => 'tuling',
);
