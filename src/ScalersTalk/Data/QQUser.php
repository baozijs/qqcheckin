<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-23 23:49:20
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-26 18:24:55
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
        $query = new Query($this->table);
        return $query->find();
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