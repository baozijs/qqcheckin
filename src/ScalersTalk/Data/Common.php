<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-23 15:55:53
 * @Last Modified by:   AminBy
 * @Last Modified time: 2018-02-16 09:27:48
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
    const PACKNUM = 1000;

    protected $table;
    public function __construct($group) {
        $this->table = $this->getTable($group);
    }

    public function getTable($group) {
        $parsedClass = explode('\\', get_class($this));
        $name = end($parsedClass);
        return 'Debug' . ucfirst($group) . ucfirst($name);
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
            if(empty($hashes)) {
                return;
            }

            $exists = [];
            $skip = 0;
            do {
                $cql = sprintf("select objectId from %s where hash in (%s) limit %d, %d"
                    , $this->table
                    , implode(', ', array_fill(0, count($hashes), '?'))
                    , $skip
                    , self::PACKNUM
                );

                $ret = Query::doCloudQuery($cql, $hashes);
                $exists = array_merge($exists, $ret['results']);
                $skip += self::PACKNUM;
            }
            while(count($ret['results']) == self::PACKNUM);
            Object::destroyAll(array_filter($exists));
            Log::debug(count($exists) . ' deleted from ' . $this->table);
        }
        catch (CloudException $ex) {
            Log::debug($cql);
            Log::debug($ex->getMessage());
        }
    }

    protected function get_exists($hashes) {
        try {
            if(empty($hashes)) {
                return [];
            }


            $skip = 0;
            $exists = [];
            do {
                $cql = sprintf("select * from %s where hash in (%s) limit %d, %d"
                    , $this->table
                    , implode(', ', array_fill(0, count($hashes), '?'))
                    , $skip
                    , self::PACKNUM
                );

                $ret = Query::doCloudQuery($cql, $hashes);
                $exists = array_merge($exists, $ret['results']);
                $skip += self::PACKNUM;
            }
            while(count($ret['results']) == self::PACKNUM);

            return $exists;
        }
        catch (CloudException $ex) {
            Log::debug($cql);
            Log::debug($ex->getMessage());
            Log::debug(json_encode(array_merge($hashes, [$skip])));
        }
        catch (RuntimeException $ex) {
            Log::debug($cql);
            Log::debug($ex->getMessage());
            Log::debug(json_encode(array_merge($hashes, [$skip])));
        }
        return [];
    }

    public function batch_save($data) {
        if(empty($data)) {
            Log::debug('empty for save ' . $this->table);
            return;
        }
        foreach($data as &$datum) {
            $datum['hash'] = static::_gen_hash($datum);
        }

        // update existed objects
        $exists = $this->get_exists(array_column($data, 'hash'));
        $data = array_column($data, null, 'hash'); // remove duplicated
        foreach ($exists as $object) {
            $hash = $object->get('hash');
            if(isset($data[$hash])) {
                $this->update($object, $data[$hash], true);
                unset($data[$hash]);
            }
        }
        Log::debug(count($exists) . ' updated from ' . $this->table);

        // new object
        $objects = array_map(function($obj) {
            return $this->create($obj, true);
        }, $data);

        $allObjs = array_merge($exists, $objects);
        Object::saveAll(array_filter($allObjs));

        Log::debug(count($objects) . ' created from ' . $this->table);
    }

    public function update($object, $datum, $batch = false) {
        try {
            if(!is_array($datum) && !is_object($datum)) {
                return;
            }

            $object = new Object($this->table);
            foreach($datum as $key => $val) {
                $object->set($key, $val);
            }
            $batch || $object->save();
        }
        catch(CloudException $ex) {
            Log::debug($ex->getMessage());
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
            Log::debug($ex->getMessage());
        }
        return $object;
    }

    protected function allWithDate($begindate, $enddate) {
        $objects = [];

        try {
            $skip = 0;
            do {
                $cql = sprintf("select * from %s where date between ? and ? limit %d, %d"
                    , $this->table
                    , $skip
                    , self::PACKNUM
                );

                $ret = Query::doCloudQuery($cql, [$begindate, $enddate]);
                $objects = array_merge($objects, $ret['results']);
                $skip += self::PACKNUM;
            }
            while(count($ret['results']) == self::PACKNUM);
        }
        catch(CloudException $e) {
            Log::debug($e->getMessage());
        }

        return $objects;
    }

    protected function allWithDateWithQQNo($begindate, $enddate, $qqnos = null) {
        $objects = [];

        if ($qqnos) {
            if (is_array($qqnos)) {
                $qqnos = implode(",", $qqnos);
            }
        }

        try {
            $skip = 0;
            do {
                if (!$qqnos) {
                    $cql = sprintf("select * from %s where date between %d and %d limit %d, %d"
                        , $this->table
                        , $begindate
                        , $enddate
                        , $skip
                        , self::PACKNUM
                    );
                }
                else {
                    $cql = sprintf("select * from %s where date between %d and %d and qqno in (%s) limit %d, %d"
                        , $this->table
                        , $begindate
                        , $enddate
                        , $qqnos
                        , $skip
                        , self::PACKNUM
                    );
                    // die($cql);
                }

                $ret = Query::doCloudQuery($cql);
                $objects = array_merge($objects, $ret['results']);
                $skip += self::PACKNUM;
            }
            while(count($ret['results']) == self::PACKNUM);
        }
        catch(CloudException $e) {
            Log::debug($e->getMessage());
        }

        return $objects;
    }

    protected function singleWithDate($qqno, $begindate, $enddate) {
        $objects = [];

        try {

            $skip = 0;
            do {
                $cql = sprintf("select * from %s where qqno = ? and date >= ? and date <= ? limit %d, %d"
                    , $this->table
                    , $skip
                    , self::PACKNUM
                );

                $ret = Query::doCloudQuery($cql, [intval($qqno), $begindate, $enddate]);
                $objects = array_merge($objects, $ret['results']);
                $skip += self::PACKNUM;
            }
            while(count($ret['results']) == self::PACKNUM);
        }
        catch(CloudException $e) {
            Log::debug($e->getMessage());
        }

        return $objects;
    }

    public static function asArray($result) {
        is_array($result) || $result = [];
        return array_map(function($object) {
            return $object->toFullJSON();
        }, $result);
    }

    public function deleteByQQno($qqno, $field = null) {
        try {
            $field == null && $field = 'qqno';
            $skip = 0;
            do {
                $query = new Query($this->table);
                $query->equalTo($field, intval($qqno));
                $query->skip($skip);
                $query->limit(self::PACKNUM);
                $objs = $query->find();
                $count = count($objs);
                $skip += $count;
                Object::destroyAll($objs);
            }
            while($count == self::PACKNUM);
            return true;
        }
        catch (CloudException $e) {
            Log::debug($e->getMessage());
            return false;
        }
    }

    public function cleanData($field) {

        try {
            $uniqued = [];
            $all = [];
            $skip = 0;
            do {
                $query = new Query($this->table);
                $query->skip($skip);
                $query->limit(self::PACKNUM);
                $objs = $query->find();
                $count = count($objs);
                $skip += self::PACKNUM;

                foreach($objs as $obj) {
                    $all[] = $obj->get('objectId');
                    $uniqued[$obj->get($field)] = $obj->get('objectId');
                }
            }
            while($count == self::PACKNUM);

            $diff = array_diff($all, $uniqued);
            $objs = array_map(function($objId) {
                return Object::create($this->table, $objId);
            }, $diff);
            Object::destroyAll($objs);
            Log::debug($this->table . " cleanData " . count($objs));
            return true;
        }
        catch (CloudException $e) {
            Log::debug($e->getMessage());
            return false;
        }
    }
}
