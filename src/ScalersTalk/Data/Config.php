<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-23 17:13:12
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-30 00:20:29
 */
namespace ScalersTalk\Data;

use \LeanCloud\Query;
use \LeanCloud\Object;
use \LeanCloud\CloudException;

class Config extends Common {
    function get($key) {
        try {
            $query = new Query($this->table);
            $query->equalTo("key", $key);
            $obj = $query->first();
            return $obj->get('value');
        }
        catch (CloudException $e) {
            error_log(" config {$key} get fail");
        }
    }

    function set($key, $value) {
        try {
            $query = new Query($this->table);
            $query->equalTo("key", $key);
            $obj = $query->first();
        }
        catch (CloudException $e) {
            error_log(" config {$key} get for update fail");
        }

        try {
            if(empty($obj)) {
                $obj = new Object($this->table);
                $obj->set('key', $key);
            }
            $obj->set('value', $value);
            $obj->save();
        }
        catch (CloudException $e) {
            error_log(" config {$key} {$value} save fail");
        }
    }
}