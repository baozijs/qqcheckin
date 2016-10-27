<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-24 01:18:54
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-28 00:49:41
 */
namespace ScalersTalk\Setting;

class Config {
    static $SETTINGS = [
        'groups' => [
            "kuang" => '狂练',
            "ling" => '零听',
        ]
    ];

    public static function get($key) {
        return empty(self::$SETTINGS[$key]) ? [] : self::$SETTINGS[$key];
    }
}
