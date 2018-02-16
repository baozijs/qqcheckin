<?php

/**
 * @Author: AminBy
 * @Date:   2018-01-12 23:26:02
 * @Last Modified by:   AminBy
 * @Last Modified time: 2018-02-16 08:24:31
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
        self::asArray($this->loadUsers());
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
        return $query->first();
    }
}