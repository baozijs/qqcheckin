<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-16 16:52:25
 * @Last Modified by:   AminBy
 * @Last Modified time: 2017-07-31 21:34:48
 */

namespace ScalersTalk\Util;

class ChatParser {

    const RE_WHO = "/^(?P<when>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\s+(?P<nick>.*)\((?P<qqno>[1-9][0-9]{4,})\)$/i"; // 2016-07-03 09:04:00  Steve (2276064083)
    const RE_SELF = "/^(?P<when>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\s+(?P<nick>.*)$/i"; // 2016-07-03 09:04:00  Steve 
    static $RE_IGNORES = ['/\*.+@$/i'];
    static $COMPACIBILITY = [
        '【' => '[',
        '】' => ']',
        '［' => '[',
        '］' => ']',
        '～' => '~',
        '－' => '-',
        '　' => ' ',
        '．' => '.',
        '＋' => '+',
        ];
    // 
    private $re_checkin_date = "(?P<month>\d{1,2})[\.\-]?(?P<day>\d{1,2})[\s\-]*";
    private $re_checkin_item = "(?P<item>%s)";
    private $re_checkin_rate = "[\s\-]*(?P<rate>(\d{1,2}(?:\.\d{1,2})?)|100)\%?";

    private $content;
    private $lastUpdate;
    private $currentUpdate;
    private $item_key_map;
    private $item_valid_map;
    public function __construct($path, $items, $lastUpdate) {
        if(!is_file($path)) {
            die($path . ' is not a valid file.');
        }
        $hfile = fopen($path, 'r');
        $this->content = strtr(fread($hfile, filesize($path)), self::$COMPACIBILITY);
        fclose($hfile);

        $this->lastUpdate = empty($lastUpdate) ? 0 : $lastUpdate - 30;

        $items = array_map(function($i, $k){
            return [$i['name'], $i['valid'], $k];
        }, $items, array_keys($items));

        $this->items = array_column($items, 0);
        $this->item_key_map = array_combine($this->items, array_column($items, 2));
        $this->item_valid_map = array_combine($this->items, array_column($items, 1));
        $this->re_checkin_item = sprintf($this->re_checkin_item, implode("|", $this->items));
    }

    public function parse() {
        $this->parse_step1_split();
        $this->parse_step2_tokens();
        $this->parse_step3_checkins_leaves();
        // $this->parse_step4_remove_duplicated();
    }

    private static function _to_time() {
        if(func_num_args() == 3) {
            list($month, $day, $when) = func_get_args();
        }
        else {
            list($month_day, $when) = func_get_args();
            if(preg_match('/^(\d{1,2})[\.\-]?(\d{1,2})$/i', $month_day, $match)) {
                list($_, $month, $day) = $match;
            }
            else {
                return strtotime('midnight');
            }
        }

        $time = strtotime(sprintf('%d-%d-%d', date('Y', $when), $month, $day));
        if($time < $when && abs($when - $time) > 2592000) {
            $time = strtotime('+1 year', $time);
        }
        elseif ($time > $when && abs($when - $time) > 2592000) {
            $time = strtotime('-1 year', $time);
        }
        return $time;
    }

    private static function _if_ignore($line) {
        if(empty($line)) {
            return true;
        }
        foreach(self::$RE_IGNORES as $re_ignore) {
            if(preg_match($re_ignore, $line)) {
                return true;
            }
        }
        return false;
    }

    private static function _chatFilter($line) {
        $line = preg_replace('/\][^\[]+\[/i', '][', $line);
        $line = preg_replace('/(^[^\[]+)|([^\]]+$)/i', '', $line);
        return $line;
    }

    protected function parse_checkin($checkin, $chat, $qqno, $chats_raw, $index) {
        static $is_valid = null;
        $is_valid || $is_valid = function($checkin, $chatsraw, $index) {
            $valid =& $this->item_valid_map[$checkin['item']];
            if(!$valid) {
                return true;
            }
            // $chatsraw =& $this->qqno_chats;
            $chat =& $chatsraw[$index]['chat'];
            $valid_item = sprintf('[%s]', $valid);
            // if valid before or follow token
            if(strpos($chat, $checkin['origin'].$valid_item) >= 0
                || strpos($chat, $valid_item.$checkin['origin']) >= 0) {
                return true;
            }

            if(
                $index > 0
                && abs($chatsraw[$index]['when'] - $chatsraw[$index-1]['when']) < 30
                && $chatsraw[$index-1]['chat'] == $valid_item

                || $index < count($chatsraw)
                && abs($chatsraw[$index]['when'] - $chatsraw[$index+1]['when']) < 130
                && $chatsraw[$index+1]['chat'] == $valid_item
            ) {
                return true;
            }

            return false;
        };


        $checkin['qqno'] = $qqno;
        $checkin['itemkey'] = $this->item_key_map[$checkin['item']];
        $checkin['date'] = self::_to_time($checkin['month'], $checkin['day'], $chat['when']);
        $checkin['when'] = $chat['when'];
        $checkin['rate'] = floatval($checkin['rate']);

        // 无相应的资料记录无效 补5天以上的补卡无效
        $checkin['isvalid'] = $is_valid($checkin, $chats_raw, $index) && abs(strtotime(date('Y-m-d', $chat['when'])) - $checkin['date']) < 518400;
        // 是否补卡
        // $checkin['isfill'] = date('Ymd', $checkin['date']) != date('Ymd', $chat['when']);
        $checkin['isfill'] = date('Ymd', $checkin['date']) < date('Ymd', $chat['when']);
        // unset($checkin['month']);
        // unset($checkin['day']);
        // unset($checkin['origin']);
        $checkin['date_show'] = date('Y-m-d H:i:s', $checkin['date']);
        $checkin['when_show'] = date('Y-m-d H:i:s', $checkin['when']);

        return $checkin;
    }

    protected function parse_leave($leave, $chat, $qqno, $chats_raw, $index) {
        $when = $chat['when'];

        $ret = [];

        $leave = preg_replace('#\s*请假\s*#i', ' ', $leave);
        $leave = trim(preg_replace('#\[(.*)\]#', '\1', $leave));
        $leave = trim(preg_replace('#[\+\|]#', ' ', $leave));
        $leave = trim(preg_replace('#\s*[\-\~]\s*#', '-', $leave));
        $leave = trim(preg_replace('#\s+#', ' ', $leave));
        $leave = explode(' ', $leave);

        $getDatetimes = function($dateStr) use ($when) {
            if(strpos($dateStr, '-') > -1) {
                $arr = explode('-', $dateStr);
                list($b, $e) = [$this->_to_time(reset($arr), $when), $this->_to_time(end($arr), $when)];
                return range($b, $e, 86400);
            }
            else {
                return [$this->_to_time($dateStr, $when)];
            }
        };
        $params = array_map(function($str) use($getDatetimes) {
            return $getDatetimes($str);
        }, $leave);

        $vs = strtotime('today', $when);
        $ve = strtotime('+1 month', $when);
        return array_map(function($date) use ($qqno, $when, $vs, $ve) {
            $isvalid = $date >= $vs && $date <= $ve;
            return [
                'item' => '请假',
                'itemkey' => 'leave',
                'qqno' => $qqno,
                'when' => intval($when),
                'isvalid' => $isvalid,
                'date' => intval($date),
            ];
        }, call_user_func_array('array_merge', $params));
    }

    public $checkins = [];
    public $leaves = [];
    protected function parse_step3_checkins_leaves() {
        foreach($this->qqno_chats as $qqno => $chats_raw) {
            foreach($chats_raw as $index => $chat) {
                foreach($chat['checkin'] as $checkin) {
                    $this->checkins[] = $this->parse_checkin($checkin, $chat, $qqno, $chats_raw, $index);
                }
                foreach($chat['leave'] as $leave) {
                    $this->leaves = array_merge(
                        $this->leaves,
                        $this->parse_leave($leave, $chat, $qqno, $chats_raw, $index)
                    );
                }
            }
        }
    }

    protected $qqno_chats = [];
    protected function parse_step2_tokens() {
        $ckPatt = sprintf("\s*(?:%s)?%s(?:%s)?\s*", $this->re_checkin_date, $this->re_checkin_item, $this->re_checkin_rate);
        $ckPatt = "#\[" . str_replace('#', '\\#', $ckPatt) . "\]#i";

        $lvPatt = "#\[\s*请假[^\]]*\]#i";

        foreach($this->qqno_chats_raw as $qqno => $chats_raw) {
            $qqchat = [];
            foreach($chats_raw as $when => $chat_raw) {
                $item = [
                    'when' => $when,
                    'chat' => $this->_chatFilter($chat_raw),
                    'checkin' => [],
                    'leave' => [],
                ];
                if(empty($item['chat'])) {
                    continue;
                }

                // checkin
                $matched = preg_match_all($ckPatt, $chat_raw, $matches);
                if($matched) {
                    $matches['origin'] = $matches[0];
                    $matches = array_intersect_key($matches, array_flip(['origin', 'month','day','item','rate']));
                    $checkins = [];
                    $items =& $matches['item'];
                    $months =& $matches['month'];
                    $days =& $matches['day'];
                    $rates =& $matches['rate'];
                    $origins =& $matches['origin'];
                    foreach($items as $index => $name) {
                        $checkins[] = [
                            'origin' => $origins[$index],
                            'month' => empty($months[$index]) ? date('m', $when) : $months[$index],
                            'day' => empty($days[$index]) ? date('d', $when) : $days[$index],
                            'item' => $name,
                            'rate' => empty($rates[$index]) ? 0 : $rates[$index],
                        ];
                    }
                    $item['checkin'] = $checkins;
                }

                // take a leave
                $matched = preg_match_all($lvPatt, $chat_raw, $matches);
                if($matched) {
                    $item['leave'] = $matches[0];
                }

                // save if not empty
                if(!empty($item['checkin']) || !empty($item['leave'])) {
                    $this->qqno_chats[$qqno][] = $item;
                }
            }
            if(!empty($qqchat)) {
                $this->qqno_chats[$qqno] = $qqchat;
            }
        }
    }
    
    protected $qqno_nicks = [];
    protected $qqno_chats_raw = [];
    protected function parse_step1_split() {
        $lines = explode("\n", str_replace("\r", "", $this->content));

        $qqno = null;
        $when = null;
        foreach($lines as $line) {
            $line = trim(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $line));
            if(static::_if_ignore($line)) {
                continue;
            }

            if(preg_match(self::RE_WHO, $line, $match)) {
                $qqno = $match['qqno'];
                $when = strtotime($match['when']);
                if($when <= $this->lastUpdate) {
                    continue;
                }
                $this->currentUpdate = $when;
                $this->qqno_nicks[$qqno]  = $match['nick'];
            }
            elseif(preg_match(self::RE_SELF, $line, $match)) {
                $qqno = '999999999';
                $when = strtotime($match['when']);
                if($when <= $this->lastUpdate) {
                    continue;
                }
                $this->qqno_nicks[$qqno]  = $match['nick'];
            }
            elseif($qqno !== null && $when !== null) {
                if($when <= $this->lastUpdate) {
                    continue;
                }
                if(empty($this->qqno_chats_raw[$qqno][$when])) {
                    $this->qqno_chats_raw[$qqno][$when] = '';
                }
                $this->qqno_chats_raw[$qqno][$when] .= $line; 
            }
        }
    }

    public function getQqnoNicks() {
        return $this->qqno_nicks;
    }
    public function getQqusers() {
        return array_map(function($qqno, $nick) {
            return compact('qqno', 'nick');
        }
        , array_keys($this->qqno_nicks)
        , array_values($this->qqno_nicks));
    }
    public function getQqnoChatRaws() {
        return $this->qqno_chats_raw;
    }
    public function getQqnoChats() {
        return $this->qqno_chats;
    }
    public function getChechins() {
        return $this->checkins;
    }

    public function getCurrentUpdate() {
        return $this->currentUpdate;
    }
}