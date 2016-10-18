<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-16 16:52:25
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-18 14:14:26
 */
class ChatParser {

    const RE_WHO = "/^(?P<when>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\s+(?P<nick>.*)\((?P<qqno>\d{5,13})\)$/i"; // 2016-07-03 09:04:00  Steve (2276064083)
    const RE_SELF = "/^(?P<when>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\s+(?P<nick>.*)$/i"; // 2016-07-03 09:04:00  Steve 
    const RE_IGNORES = ['/\*.+@$/i'];
    // []
    const RE_CHECKIN_DATE = "(?P<month>\d{1,2})[\.\-]?(?P<day>\d{1,2})[\s\-]*";
    const RE_CHECKIN_ITEM = "(?P<item>听写|笔记|朗读|造句|复述|周复盘)";
    const RE_CHECKIN_RATE = "[\s\-]*(?P<rate>\d{1,2}|100)";
    const RE_PATT = [
        '听写' => [],
        '笔记' => [],
        '朗读' => [],
        '造句' => [],
        '复述' => [],
        '周复盘' => [],
    ];

    private $content;
    public function __construct($path) {
        if(!is_file($path)) {
            die($path . ' is not a valid file.');
        }
        $hfile = fopen($path, 'r');
        $this->content = fread($hfile, filesize($path));
        fclose($hfile);
    }

    public function parse() {
        $this->parse_step1();
        $this->parse_step2();
    }

    private static function _if_ignore($line) {
        if(empty($line)) {
            return true;
        }
        foreach(self::RE_IGNORES as $re_ignore) {
            if(preg_match($re_ignore, $line)) {
                return true;
            }
        }
        return false;
    }

    protected $qqno_checkins = [];
    protected function parse_step2() {
        $ckPatt = sprintf("\s*(?:%s)?%s(?:%s)?\s*", self::RE_CHECKIN_DATE, self::RE_CHECKIN_ITEM, self::RE_CHECKIN_RATE);
        $ckPatt = str_replace('#', '\\#', $ckPatt);

        $ckPatt1 = "#\[${ckPatt}\]#i";
        $ckPatt2 = "#【${ckPatt}】#i";
        // echo $ckPatt1 . PHP_EOL;die;
        foreach($this->qqno_chats as $qqno => $chats) {
            $this->qqno_checkins[$qqno] = [];
            foreach($chats as $when => $chat) {
                $item = [
                    'when' => $when,
                    'chat' => $chat,
                ];
                $matches = [];
                if(preg_match_all($ckPatt1, $chat, $matches1)) {
                    $matches = $matches1;
                }
                if(preg_match_all($ckPatt2, $chat, $matches2)) {
                    $matches += $matches2;
                }
                if(!empty($matches)) {
                    $matches = array_intersect_key($matches, array_flip(['month','day','item','rate']));
                    $checkins = [];
                    $items = $matches['item'];
                    $months = $matches['month'];
                    $days = $matches['day'];
                    $rates = $matches['rate'];
                    foreach($items as $index => $name) {
                        $checkins[] = [
                            'month' => empty($months[$index]) ? date('m') : $months[$index],
                            'day' => empty($days[$index]) ? date('d') : $days[$index],
                            'item' => $name,
                            'rate' => empty($rates[$index]) ? 0 : $rates[$index],
                        ];
                    }
                    $item['checkin'] = $checkins;
                    $this->qqno_checkins[$qqno][] = $item;
                }
            }
        }
    }
    
    protected $qqno_nick = [];
    protected $qqno_chats = [];
    protected function parse_step1() {
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
                $this->qqno_nick[$qqno]  = $match['nick'];
            }
            elseif(preg_match(self::RE_SELF, $line, $match)) {
                $qqno = '999999999';
                $when = strtotime($match['when']);
                $this->qqno_nick[$qqno]  = $match['nick'];
            }
            elseif($qqno !== null && $when !== null) {
                if(empty($this->qqno_chats[$qqno][$when])) {
                    $this->qqno_chats[$qqno][$when] = '';
                }
                $this->qqno_chats[$qqno][$when] .= $line; 
            }
        }
    }

    public function getQqnoNick() {
        return $this->qqno_nick;
    }
    public function getQqnoChats() {
        return $this->qqno_chats;
    }
    public function getQqnoCheckins() {
        return $this->qqno_checkins;
    }
}