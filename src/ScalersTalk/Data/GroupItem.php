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

    public function saveGroupItem($gid, $item) {
        $query = new Query($this->table);
        $query->equalTo('gid', $gid);
        $query->equalTo('key', $item['key']);

        try {
            if ($query->count() > 0) {
                $obj = $query->first();
            }
            else {
                $obj = new Object($this->table);
                $obj->set('key', $item['key']);
                $obj->set('gid', $gid);
            }
            $obj->set('name', $item['name']);
            $obj->set('valid', empty($item['valid']) ? "" : $item['valid']);
            $obj->set('icon', empty($item['icon']) ? "" : $item['icon']);
            $obj->save();
        }
        catch (CloudException $ex) {
            Log::debug($ex->getMessage());
            return false;
        }

        return true;
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
                'key' => $item->get('key'),
                'name' => $item->get('name'),
                'icon' => $item->get('icon'),
                'valid' => empty($item->get('valid')) ? false : $item->get('valid')
            ];
        }
        return $ret;
    }
}