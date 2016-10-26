<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-23 15:55:53
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-24 00:56:30
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

    protected $table;
    public function __construct($group) {
        $parsedClass = explode('\\', get_class($this));
        $name = end($parsedClass);
        $this->table = ucfirst($group) . ucfirst($name).(constant('DEBUG') ? 'Debug' : '');
    }

    protected static function _gen_hash($datum) {
        return md5($datum['itemkey'] .$datum['qqno'] . $datum['date']);
    }

    protected function remove_exists($hashes) {
        try {
            $cql = sprintf("select objectId from %s where hash in (%s)"
                , $this->table
                , implode(', ', array_fill(0, count($hashes), '?'))
            );
            $ret = Query::doCloudQuery($cql, $hashes);
            Object::destroyAll($ret['results']);
        }
        catch (CloudException $ex) {
            error_log($cql);
            error_log($ex->getMessage());
        }
    }

    protected function get_exists($hashes) {
        try {
            $cql = sprintf("select * from %s where hash in (%s)"
                , $this->table
                , implode(', ', array_fill(0, count($hashes), '?'))
            );
            $ret = Query::doCloudQuery($cql, $hashes);
            return $ret['results'];
        }
        catch (CloudException $ex) {
            error_log($cql);
            error_log($ex->getMessage());
        }
        return [];
    }

    public function batch_save($data) {
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

        Object::saveAll(array_merge($exists, $objects));
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

    public function allWithDate($begindate, $enddate) {
        $query1 = new Query($this->table);
        $query1->greaterThanOrEqualTo('date', $statdate);

        $query2 = new Query($this->table);
        $query2->lessThanOrEqualTo('date', $enddate);

        $query = new Query($this->table);
        $query->andQuery($query1, $query2);

        return $query->find();
    }

    public function singleWithDate($qqno, $begindate, $enddate) {
        $query1 = new Query($this->table);
        $query1->greaterThanOrEqualTo('date', $statdate);

        $query2 = new Query($this->table);
        $query2->lessThanOrEqualTo('date', $enddate);

        $query3 = new Query($this->table);
        $query3->lessThanOrEqualTo('qqno', $qqno);

        $query = new Query($this->table);
        $query->andQuery($query1, $query2, $query3);

        return $query->find();
    }
}
