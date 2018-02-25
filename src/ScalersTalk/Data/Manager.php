<?php

/**
 * @Author: AminBy
 * @Date:   2018-01-12 23:26:02
 * @Last Modified by:   AminBy
 * @Last Modified time: 2018-02-21 23:51:41
 */
namespace ScalersTalk\Data;

use \LeanCloud\Query;
use \LeanCloud\Object;
use \LeanCloud\CloudException;

class Manager extends Common {
    public function __construct() {
        $this->table = 'CheckinManager';
    }

    public function loadUsersAsArray($filter = null) {
        return self::asArray($this->loadUsers());
    }

    public function loadUsers($filter = null) {
        $query = new Query($this->table);
        return $query->find();
    }

    public function fetchAsArray($name, $pass) {
        $user = $this->fetch($name, $pass);
        if ($user) {
            $user = $user->toFullJSON();
        }
        return $user;
    }

    public function fetch($name, $pass) {
        $query = new Query($this->table);
        $query->equalTo('name', $name);
        $query->equalTo('pass', md5($pass));
        return $query->count() ? $query->first() : null;
    }

    public function saveManager($manager) {
        $query = new Query($this->table);
        $query->equalTo('name', $manager['name']);

        try {
            if ($query->count() > 0) {
                $obj = $query->first();
                if (!empty($manager['pass'])) {
                    $obj->set('pass', md5($manager['pass']));
                }
            }
            else {
                $obj = new Object($this->table);
                $obj->set('name', $manager['name']);
                empty($manager['pass']) && $manager['pass'] = 'scalerstalk';
                $obj->set('pass', md5($manager['pass']));
            }
            $obj->set('note', $manager['note']);
            $obj->set('sadmin', !!$manager['sadmin']);
            $obj->save();
        }
        catch (CloudException $ex) {
            Log::debug($ex->getMessage());
            return false;
        }

        return true;
    }
}