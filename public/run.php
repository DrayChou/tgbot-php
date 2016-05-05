<?php
/**
 * @Author: dray
 * @Date:   2016-05-03 09:29:59
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-03 14:45:31
 */

//加载包文件
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'init.php';

// 设置 token
Common::set_config('token', '87628676:AAFacIOCzRaQUpKu3XXrCoTf1kgC-SUJTug');

// 开始循环处理数据
while (true) {
    Process::run();
}
