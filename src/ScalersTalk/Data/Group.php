<?php
/**
 * @Author: AminBy
 * @Date:   2018-01-12 23:22:08
 * @Last Modified by:   AminBy
 * @Last Modified time: 2018-02-16 09:03:57
 */
namespace ScalersTalk\Data;

use \LeanCloud\Query;
use \LeanCloud\Object;
use \LeanCloud\CloudException;

// [gid] => 'kuang'
// [gname] => '狂练'

class Group extends Common {
    public function __construct() {
        $this->table = 'CheckinGroup';
    }

    public function groups($filter) {
        return array_column($this->loadGroups(), 'gname', 'gid');
    }

    public function queryByManagerAsArray($managerName) {
        return self::asArray($this->queryByManager($managerName));
    }
    public function queryByManager($managerName) {
        $query = new Query($this->table);
        $query->equalTo('managers', $managerName);
        $query->equalTo("closed", false);
        return $query->find();
    }

    public function queryAllAsArray() {
        return self::asArray($this->queryAll());
    }
    public function queryAll() {
        $query = new Query($this->table);
        $query->equalTo("closed", false);
        return $query->find();
    }
}