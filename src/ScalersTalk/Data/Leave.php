<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-23 12:09:39
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-26 12:01:32
 */
namespace ScalersTalk\Data;

// [item] => 请假
// [itemkey] => leave
// [qqno] => 547096523
// [when] => 1476718628
// [isvalid] => 1
// [date] => 1476806400

class Leave extends Common{
    public function allWithDate($begindate, $enddate) {
        return parent::allWithDate($begindate, $enddate);
    }
    public function singleWithDate($qqno, $begindate, $enddate) {
        return parent::singleWithDate($qqno, $begindate, $enddate);
    }
}