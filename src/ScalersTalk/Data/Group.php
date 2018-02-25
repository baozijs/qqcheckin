<?php
/**
 * @Author: AminBy
 * @Date:   2018-01-12 23:22:08
 * @Last Modified by:   AminBy
 * @Last Modified time: 2018-02-22 00:00:05
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

    public function getOne($gid) {
        try {
            $query = new Query($this->table);
            $query->equalTo('gid', $gid);
            return $query->first();
        }
        catch (CloudException $ex) {
            Log::debug($ex->getMessage());
            return;
        }
    }

    public function getOneAsArray($gid) {
        $ret = $this->getOne($gid);
        if (!!$ret) {
            return $ret->toFullJSON();
        }
    }

    public function saveGroup($group) {
        $query = new Query($this->table);
        $query->equalTo('gid', $group['gid']);

        try {
            $group['closed'] = !empty($group['closed']) && in_array(strtolower($group['closed']), ["true", "yes", "1", "on", "t", "y"]);
            if ($query->count() > 0) {
                $obj = $query->first();
            }
            else {
                $obj = new Object($this->table);
                $obj->set('gid', $group['gid']);
            }
            $obj->set('gname', $group['gname']);
            $obj->set('closed', $group['closed']);
            $obj->save();
        }
        catch (CloudException $ex) {
            Log::debug($ex->getMessage());
            return false;
        }

        return true;
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

    public function queryAllAsArray($includeClosed = false) {
        return self::asArray($this->queryAll($includeClosed));
    }
    public function queryAll($includeClosed = false) {
        $query = new Query($this->table);
        if (!$includeClosed) {
            $query->equalTo("closed", false);
        }
        return $query->find();
    }

    public function saveGroupManager($isAdd, $gid, $username) {
        $query = new Query($this->table);
        $query->equalTo('gid', $gid);

        try {
            $obj = $query->first();
            $obj->addUniqueIn("managers", $username);
            $obj->save();
        }
        catch (CloudException $ex) {
            Log::debug($ex->getMessage());
            return false;
        }

        return true;
    }
}