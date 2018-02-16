<?php
/**
* @Author: AminBy
* @Date:   2018-02-16 09:08:07
* @Last Modified by:   AminBy
* @Last Modified time: 2018-02-16 09:19:19
*/
namespace ScalersTalk\Data;

use \LeanCloud\Query;
use \LeanCloud\Object;
use \LeanCloud\CloudException;

// [gid] => 'kuang'
// [gname] => '狂练'

class GroupItem extends Common {
    public function __construct() {
        $this->table = 'CheckinGroupItem';
    }

    public function fetchByGid($gid) {
        $query = new Query($this->table);
        $query->equalTo('gid', $gid);
        return $query->find();
    }

    public function fetchByGidAsArray($gid) {
        $ret = [];
        foreach($this->fetchByGid($gid) as $item) {
            $ret[$item->get('key')] = [
                'name' => $item->get('name'),
                'valid' => empty($item->get('valid')) ? false : $item->get('valid')
            ];
        }
        return $ret;
    }
}