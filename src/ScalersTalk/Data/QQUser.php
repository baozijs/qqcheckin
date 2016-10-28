<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-23 23:49:20
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-28 12:24:27
 */
namespace ScalersTalk\Data;

use \LeanCloud\Query;
use \LeanCloud\Object;
use \LeanCloud\CloudException;
 
// [qqno] => 听写
// [nick] => 89

class QQUser extends Common {

    protected static function _gen_hash($datum) {
        return md5($datum['qqno']);
    }

    public function all() {
        $ret = [];
        $skip = 0;
        do {
            $query = new Query($this->table);
            $query->skip($skip);
            $tmp = $query->find();
            $ret = array_merge($ret, $tmp);

            $skip += 100;
        }
        while(count($tmp) == 100);
        return $ret;
    }

    public function nick($qqno) {
        try {
            $query = new Query($this->table);
            $query->equalTo("qqno", intval($qqno));
            $obj = $query->first();
            return $obj->get('nick');
        }
        catch (CloudException $e) {
            die($qqno . ' not found.');
        }
    }
}