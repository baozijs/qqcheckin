<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-23 23:49:20
 * @Last Modified by:   AminBy
 * @Last Modified time: 2018-03-12 21:57:22
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
        try {
            $skip = 0;
            do {
                $query = new Query($this->table);
                $query->limit(static::PACKNUM)->skip($skip);
                $tmp = $query->find();
                $ret = array_merge($ret, $tmp);

                $skip += static::PACKNUM;
                error_log(sprintf("qquser, ret count %d, tmp count: %d", count($ret), count($tmp)));
            }
            while(count($tmp) == static::PACKNUM);
        }catch(CloudException $e) {
            Log::debug($e->getMessage());
        }
        error_log("qquser count " . count($ret));
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
            Log::debug($qqno . ' not found.');
        }
    }

    public function remove($qqno) {
        
    }
}