<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-23 23:49:20
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-24 00:55:50
 */
namespace ScalersTalk\Data;
 
// [item] => 听写
// [rate] => 89
// [qqno] => 254074593
// [itemkey] => dictate
// [date] => 1477152000
// [when] => 1476714131
// [isvalid] =>
// [isfill] => 1

class QQUser extends Common {

    protected static function _gen_hash($datum) {
        return md5($datum['qqno']);
    }
}