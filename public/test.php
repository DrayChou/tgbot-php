<?php
/**
 * @Author: dray
 * @Date:   2016-05-03 09:29:59
 * @Last Modified by:   dray
 * @Last Modified time: 2016-05-05 10:28:19
 */

//加载包文件
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'init.php';

// 设置 token
Common::set_config('token', '87628676:AAFacIOCzRaQUpKu3XXrCoTf1kgC-SUJTug');

$router = Common::get_router();
var_dump($router);

// $me = Telegram::singleton()->get_me();
// var_dump($me);

// $me = Db::get_bot_info();
// var_dump($me);

// $me = Db::get_bot_name();
// var_dump($me);

// $me = Db::get_update_id();
// var_dump($me);

// $URLStyle = "http://flash.weather.com.cn/wmaps/xml/%s.xml";
// $chinaURL = sprintf($URLStyle, "china");
// $chinaStr = file_get_contents($chinaURL);
// $chinaObj = simplexml_load_string($chinaStr);
// $chinaObjLen = count($chinaObj->city);
// echo "chinaObjLen = " . $chinaObjLen . "\n";
// for ($i = 0; $i < $chinaObjLen; $i++) {
// //遍历省一级节点，共37个
//     $level1 = $chinaObj->city[$i]["pyName"];
//     $shengjiURL = sprintf($URLStyle, $level1);
//     $shengjiStr = file_get_contents($shengjiURL);
//     //echo $shengjiStr;
//     $shengjiObj = simplexml_load_string($shengjiStr);
//     $shengjiObjLen = count($shengjiObj->city);
// //      echo $chinaObj->city[$i]["quName"];
//     //      echo " ".$shengjiObjLen."\n";
//     for ($j = 0; $j < $shengjiObjLen; $j++) {
//         //遍历市一级节点
//         $level2 = $shengjiObj->city[$j]["pyName"];
//         $shijiURL = sprintf($URLStyle, $level2);
//         $shijiStr = file_get_contents($shijiURL);
//         //echo $shijiStr;
//         $shijiObj = simplexml_load_string($shijiStr);
//         //直辖市和海南、台湾、钓鱼岛等没有县级节点
//         if (!$shijiObj) {
//             echo "WARNNING: not exsit next level node. - " . $level1 . "-" . $shijiURL . "\n";
//             echo '  "' . $shengjiObj->city[$j]["cityname"] . '" => ';
//             echo $shengjiObj->city[$j]["url"] . ",\n";
//             continue;
//         }
//         $shijiObjLen = count($shijiObj->city);
//         //echo $shengjiObj->city[$j]["cityname"]."  ";
//         //echo $shijiObjLen."\n";
//         for ($k = 0; $k < $shijiObjLen; $k++) {
//             //遍历县一级节点
//             $xianji_code = $shijiObj->city[$k]["url"];
//             echo '  "' . $shijiObj->city[$k]["cityname"] . '" => ';
//             echo $shijiObj->city[$k]["url"] . ",\n";
//             //echo $xianji_code."\n";
//         }
//     }
// }
// print_r($chinaObj);

$url = "http://mobile.weather.com.cn/js/citylist.xml";
$str = file_get_contents($url);
preg_match_all('/d1="([0-9]+)" d2="([^"]+)" d3="([^"]+)" d4="([^"]+)"/', $str, $match);

$city_id_list = array();
for ($i = 0; $i < count($match[0]); $i++) {
    $city_id_list[$match[2][$i]] = array(
        $match[1][$i],
        $match[2][$i],
        $match[3][$i],
        $match[4][$i],
    );
}

print_r($city_id_list);
