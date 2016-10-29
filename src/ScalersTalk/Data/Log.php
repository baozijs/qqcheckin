<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-23 17:13:12
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-30 01:46:25
 */
namespace ScalersTalk\Data;

use \LeanCloud\Query;
use \LeanCloud\Object;
use \LeanCloud\CloudException;

class Log {
    public static function debug($data) {
        self::log('debug', $data);
    }
    public static function info($data) {
        self::log('info', $data);
    }
    public static function error($data) {
        self::log('error', $data);
    }
    public static function log($type, $data) {
        if(!is_string($data)) {
            $data = json_encode($data);
        }
        $obj = new Object('CheckinLog');
        $obj->set('type', strtoupper($type));
        $obj->set('data', $data);
        $obj->save();
    }
}