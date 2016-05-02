<?php
/**
 * Created by PhpStorm.
 * User: Dray
 * Date: 2016/5/1
 * Time: 10:17
 */

define('BOT', 'tgbot-php');
define('BASE_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('LIB_PATH', BASE_PATH . 'lib' . DIRECTORY_SEPARATOR);

//加载包文件
require_once LIB_PATH . 'process.php';

if ($handle = opendir(BASE_PATH . 'plugins' . DIRECTORY_SEPARATOR)) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            echo $file, PHP_EOL;
            $plugin = basename($file, ".php");

            require_once BASE_PATH . 'plugins' . DIRECTORY_SEPARATOR . 'base.php';
            require_once BASE_PATH . 'plugins' . DIRECTORY_SEPARATOR . $file;
            echo $plugin, PHP_EOL;
            echo $plugin::router(), PHP_EOL;
        }
    }
    closedir($handle);
}