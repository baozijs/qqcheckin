<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-23 12:09:39
 * @Last Modified by:   AminBy
 * @Last Modified time: 2017-04-23 22:19:56
 */
namespace ScalersTalk\Data;

use \LeanCloud\Query;
use \LeanCloud\Object;
use \LeanCloud\CloudException;
 
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
    public function allWithDateWithQQNo($begindate, $enddate, $qqnos = null) {
        return parent::allWithDateWithQQNo($begindate, $enddate, $qqnos);
    }
    public function singleWithDate($qqno, $begindate, $enddate) {
        return parent::singleWithDate($qqno, $begindate, $enddate);
    }
    public function getLastCheckin($qqnos) {

        $objects = [];

        foreach($qqnos as $qqno) {
            try {
                $query = new Query($this->table);
                $query->equalTo("qqno", intval($qqno))->descend('when');
                $obj = $query->first();
                $objects[$qqno] = $obj->get('when');
            }
            catch (CloudException $e) {
                Log::debug($e->getMessage());
                $objects[$qqno] = 0;
            }
        }

        return $objects;
    }
}