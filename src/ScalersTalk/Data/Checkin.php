<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-23 12:09:39
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-26 12:03:22
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

class Checkin extends Common {
    public function allWithDate($begindate, $enddate) {
        return parent::allWithDate($begindate, $enddate);
    }
    public function singleWithDate($qqno, $begindate, $enddate) {
        return parent::singleWithDate($qqno, $begindate, $enddate);
    }
}