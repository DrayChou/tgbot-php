<?php
/**
 * @Author: dray
 * @Date:   2016-05-03 14:41:42
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-15 11:34:18
 */

define('BOT', 'tgbot-php');
define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('LIB_PATH', BASE_PATH . 'lib' . DIRECTORY_SEPARATOR);
define('PLUGIN_PATH', BASE_PATH . 'plugins' . DIRECTORY_SEPARATOR);

//加载包文件
require_once LIB_PATH . 'Common.php';
require_once LIB_PATH . 'Telegram.php';
require_once LIB_PATH . 'Db.php';
require_once LIB_PATH . 'Process.php';
require_once LIB_PATH . 'Base.php';

//设置时区
date_default_timezone_set(Common::get_config('timezone', 'Asia/Shanghai'));
