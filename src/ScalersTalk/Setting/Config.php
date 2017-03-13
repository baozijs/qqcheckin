<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-24 01:18:54
 * @Last Modified by:   AminBy
 * @Last Modified time: 2017-03-13 01:09:55
 */
namespace ScalersTalk\Setting;

class Config {
    static $SETTINGS = [
        'groups' => [
            'kuang' => '狂练2016',
            'ling' => '零基础2016',
            'kuang17' => '狂练2017',
            'ling17' => '零基础2017',
            'train001' => '训练营001',
            'train002' => '训练营002',
            'train003' => '训练营003',
            'nce3' => '新概念3',
            'test' => '用于测试',
        ],
        'users' => [
            'crazy' => [
                'pass' => 'apple',
                'groups' => [
                    'kuang',
                    'kuang17',
                ],
            ],
            'zero' => [
                'pass' => 'pear',
                'groups' => [
                    'ling',
                    'ling17',
                ],
            ],
            'train001' => [
                'pass' => 'grape',
                'groups' => [
                    'train001',
                ],
            ],
            'train002' => [
                'pass' => 'blackberry',
                'groups' => [
                    'train002',
                ],
            ],
            'train003' => [
                'pass' => 'watermelon',
                'groups' => [
                    'train003',
                ],
            ],
            'nce3' => [
                'pass' => 'durian',
                'groups' => [
                    'nce3',
                ],
            ],
            'manager' => [
                'pass' => 'banana',
                'groups' => [
                    'kuang',
                    'ling',
                    'kuang17',
                    'ling17',
                    'train001',
                    'train002',
                    'train003',
                ],
            ],
            'superadmin' => [
                'pass' => 'password',
                'groups' => 'ALL',
            ],
        ],
    ];

    public static function get($key) {
        return empty(self::$SETTINGS[$key]) ? [] : self::$SETTINGS[$key];
    }
}
