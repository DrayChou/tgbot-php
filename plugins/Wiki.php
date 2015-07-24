<?php

/**
 * wiki 查询
 * User: dray
 * Date: 15/7/10
 * Time: 下午3:43
 */
class Wiki extends Base
{
    static function desc() {
        return "/wiki - Searches Wikipedia and send results";
    }

    static function usage() {
        return array(
            "/wiki [text]: Read extract from default Wikipedia (EN)",
            "/wiki [lang] [text]: Read extract from 'lang' Wikipedia. Example: !wikies hola",
            "/wiki search [text]: Search articles on default Wikipedia (EN)",
            "/wiki [lang] search [text]: Search articles on 'lang' Wikipedia. Example: !wikies search hola",
        );
    }

    /**
     * 当命令满足的时候，执行的基础执行函数
     * @throws Exception
     */
    public function run() {
        CFun::echo_log("执行 Wiki run ");

        //如果是需要回掉的请求
        if (empty($this->text)) {
            $this->set_reply();

            return;
        }

        /**
         * [parms] => Array
         * (
         * [0] => wiki
         * [1] => zh
         * [2] => search
         * [3] => 北京
         * )
         */

        $lang     = 'en';
        $lang_arr = array('aa', 'ab', 'ace', 'af', 'ak', 'als', 'am', 'an', 'ang', 'ar', 'arc', 'arz', 'as', 'ast', 'av', 'ay', 'az', 'ba', 'bar', 'bat-smg', 'bcl', 'be', 'be-x-old', 'bg', 'bh', 'bi', 'bjn', 'bm', 'bn', 'bo', 'bpy', 'br', 'bs', 'bug', 'bxr', 'ca', 'cbk-zam', 'cdo', 'ce', 'ceb', 'ch', 'cho', 'chr', 'chy', 'ckb', 'co', 'cr', 'crh', 'cs', 'csb', 'cu', 'cv', 'cy', 'da', 'de', 'diq', 'dsb', 'dv', 'dz', 'ee', 'el', 'eml', 'en', 'eo', 'es', 'et', 'eu', 'ext', 'fa', 'ff', 'fi', 'fiu-vro', 'fj', 'fo', 'fr', 'frr', 'frp', 'fur', 'fy', 'ga', 'gag', 'gan', 'gd', 'gl', 'glk', 'gn', 'got', 'gu', 'gv', 'ha', 'hak', 'haw', 'he', 'hi', 'hif', 'ho', 'hr', 'hsb', 'ht', 'hu', 'hy', 'hz', 'ia', 'id', 'ie', 'ig', 'ii', 'ik', 'ilo', 'io', 'is', 'it', 'iu', 'ja', 'jbo', 'jv', 'ka', 'kaa', 'kab', 'kbd', 'kg', 'ki', 'kj', 'kk', 'kl', 'km', 'kn', 'ko', 'koi', 'kr', 'krc', 'ks', 'ksh', 'ku', 'kv', 'kw', 'ky', 'la', 'lad', 'lb', 'lbe', 'lg', 'li', 'lij', 'lmo', 'ln', 'lo', 'lt', 'ltg', 'lv', 'map-bms', 'mdf', 'mg', 'mh', 'mhr', 'mi', 'mk', 'ml', 'mn', 'mo', 'mr', 'mrj', 'ms', 'mt', 'mus', 'mwl', 'my', 'myv', 'mzn', 'na', 'nah', 'nap', 'nds', 'nds-nl', 'ne', 'new', 'ng', 'nl', 'nn', 'no', 'nov', 'nrm', 'nso', 'nv', 'ny', 'oc', 'om', 'or', 'os', 'pa', 'pag', 'pam', 'pap', 'pcd', 'pdc', 'pfl', 'pi', 'pih', 'pl', 'pms', 'pnb', 'pnt', 'ps', 'pt', 'qu', 'rm', 'rmy', 'rn', 'ro', 'roa-rup', 'roa-tara', 'ru', 'rue', 'rw', 'sa', 'sah', 'sc', 'scn', 'sco', 'sd', 'se', 'sg', 'sh', 'si', 'simple', 'sk', 'sl', 'sm', 'sn', 'so', 'sq', 'sr', 'srn', 'ss', 'st', 'stq', 'su', 'sv', 'sw', 'szl', 'ta', 'te', 'tet', 'tg', 'th', 'ti', 'tk', 'tl', 'tn', 'to', 'tpi', 'tr', 'ts', 'tt', 'tum', 'tw', 'ty', 'udm', 'ug', 'uk', 'ur', 'uz', 've', 'vec', 'vep', 'vi', 'vls', 'vo', 'wa', 'war', 'wo', 'wuu', 'xal', 'xh', 'xmf', 'yi', 'yo', 'za', 'zea', 'zh', 'zh-classical', 'zh-min-nan', 'zh-yue', 'zu');

        $is_search  = false;
        $search_arr = array('s', 'search');

        $parms = array();
        foreach ($this->parms as $k => $v) {
            if (in_array($v, array('wiki'))) {
                continue;
            }

            if (in_array($v, $search_arr)) {
                $is_search = true;
                continue;
            }

            $v = strtolower($v);
            if (in_array($v, $lang_arr)) {
                $lang = $v;
                continue;
            }

            $parms[] = $v;
        }

        if ($is_search) {
            //https://en.wikipedia.org/w/api.php?&format=json&action=query&list=search&srlimit=20&srsearch=beijing&continue=
            $data = array(
                'format'   => 'json',
                'action'   => 'query',
                'list'     => 'search',
                'srlimit'  => '20',
                'continue' => '',
                'srsearch' => implode(' ', $parms),
            );
        } else {
            //https://en.wikipedia.org/w/api.php?&format=json&action=query&prop=extracts&exchars=300&redirects=1&exsectionformat=plain&explaintext=&titles=beijing
            $data = array(
                'format'          => 'json',
                'action'          => 'query',
                'prop'            => 'extracts',
                'exchars'         => '300',
                'redirects'       => 1,
                'exsectionformat' => 'plain',
                'explaintext'     => '',
                'titles'          => implode(' ', $parms),
            );
        }

        $url = "https://{$lang}.wikipedia.org/w/api.php?" . http_build_query($data);
        $res = CFun::curl($url);

        $res_str = '';
        if (!isset($res['query'])) {
            $res_str = '好像出问题了，稍后再试下吧！';
        } else {
            if ($is_search) {
                if (empty($res['query']['search'])) {
                    $res_str = 'No results found';
                } else {
                    foreach ($res['query']['search'] as $v) {
                        $res_str .= $v['title'] . PHP_EOL;
                    }
                }
            } else {
                if (empty($res['query']['pages'])) {
                    $res_str = 'No results found';
                } else {
                    foreach ($res['query']['pages'] as $v) {
                        $res_str .= (isset($v['extract']) ? $v['extract'] : $v['title']) . PHP_EOL;
                    }
                }
            }
        }

        //回复消息
        Telegram::singleton()->send_message(array(
            'chat_id'             => $this->chat_id,
            'text'                => $res_str,
            'reply_to_message_id' => $this->msg_id,
        ));
    }
}
