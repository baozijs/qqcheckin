<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-23 15:55:53
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-28 13:05:03
 */

namespace ScalersTalk\Data;

use \LeanCloud\Query;
use \LeanCloud\Object;
use \LeanCloud\CloudException;


// [item] => 请假
// [itemkey] => leave
// [qqno] => 547096523
// [when] => 1476718628
// [isvalid] => 1
// [date] => 1476806400

abstract class Common {
    const PACKNUM = 300;

    protected $table;
    public function __construct($group) {
        $parsedClass = explode('\\', get_class($this));
        $name = end($parsedClass);
        $this->table = ucfirst($group) . ucfirst($name).(constant('DEBUG') ? 'Debug' : '');
    }

    protected static $_keys = [
        'objectId', 'createdAt', 'updateAt'
    ];
    protected static $keys = [
    ];

    protected static function _gen_hash($datum) {
        return md5($datum['itemkey'] .$datum['qqno'] . $datum['date']);
    }

    protected function remove_exists($hashes) {
        try {
            $cql = sprintf("select objectId from %s where hash in (%s) limit ?, %d"
                , $this->table
                , implode(', ', array_fill(0, count($hashes), '?'))
                , self::PACKNUM
            );
            $exists = [];
            do {
                $ret = Query::doCloudQuery($cql, array_merge($hashes, [$skip]));
                $exists = array_merge($exists, $ret['results']);
                $skip += self::PACKNUM;
            }
            while(count($ret['results']) == self::PACKNUM);
            Object::destroyAll($exists);
        }
        catch (CloudException $ex) {
            error_log($cql);
            error_log($ex->getMessage());
        }
    }

    protected function get_exists($hashes) {
        try {
            $cql = sprintf("select * from %s where hash in (%s) limit ?, %d"
                , $this->table
                , implode(', ', array_fill(0, count($hashes), '?'))
                , self::PACKNUM
            );

            $skip = 0;
            $exists = [];
            do {
                $ret = Query::doCloudQuery($cql, array_merge($hashes, [$skip]));
                $exists = array_merge($exists, $ret['results']);
                $skip += self::PACKNUM;
            }
            while(count($ret['results']) == self::PACKNUM);
            return $exists;
        }
        catch (CloudException $ex) {
            error_log($cql);
            error_log($ex->getMessage());
        }
        return [];
    }

    public function batch_save($data) {
        if(empty($data)) {
            debug_print_backtrace();die;
        }
        foreach($data as &$datum) {
            $datum['hash'] = static::_gen_hash($datum);
        }

        // update existed objects
        $exists = $this->get_exists(array_column($data, 'hash'));
        $data = array_column($data, null, 'hash'); // remove duplicated
        foreach ($exists as $object) {
            $hash = $object->get('hash');
            $this->update($object, $data[$hash], true);
            unset($data[$hash]);
        }

        // new object
        $objects = array_map(function($obj) {
            return $this->create($obj, true);
        }, $data);

        $objects = array_merge($exists, $objects);
        Object::saveAll($objects);
    }

    public function update($object, $datum, $batch = false) {
        try {
            $object = new Object($this->table);
            foreach($datum as $key => $val) {
                $object->set($key, $val);
            }
            $batch || $object->save();
        }
        catch(CloudException $ex) {
            error_log($ex->getMessage());
        }
        return $object;

    }

    public function create($datum, $batch = false) {
        isset($datum['hash']) || $datum['hash'] = static::_gen_hash($datum);

        try {

            if(!$batch) {
                $exists = $this->get_exists([$datum['hash']]);
                $object = $exists[0];
            }
            else {
                $object = new Object($this->table);
            }

            foreach($datum as $key => $val) {
                $object->set($key, $val);
            }
            $batch || $object->save();
        }
        catch(CloudException $ex) {
            error_log($ex->getMessage());
        }
        return $object;
    }

    protected function allWithDate($begindate, $enddate) {

        $cql = sprintf("select * from %s where date between ? and ? limit ?, %d"
            , $this->table
            , self::PACKNUM
        );

        $objects = [];
        $skip = 0;
        do {
            $ret = Query::doCloudQuery($cql, [$begindate, $enddate, $skip]);
            $objects = array_merge($objects, $ret['results']);
            $skip += self::PACKNUM;
        }
        while(count($ret['results']) == self::PACKNUM);

        return $objects;
    }

    protected function singleWithDate($qqno, $begindate, $enddate) {

        $cql = sprintf("select * from %s where qqno = ? and date >= ? and date <= ? limit ?, %d"
            , $this->table
            , self::PACKNUM
        );

        $objects = [];
        $skip = 0;
        do {
            $ret = Query::doCloudQuery($cql, [intval($qqno), $begindate, $enddate, $skip]);
            $objects = array_merge($objects, $ret['results']);
            $skip += self::PACKNUM;
        }
        while(count($ret['results']) == self::PACKNUM);
    }

    public static function asArray($result) {
        return array_map(function($object) {
            return $object->toFullJSON();
        }, $result);
    }
}
