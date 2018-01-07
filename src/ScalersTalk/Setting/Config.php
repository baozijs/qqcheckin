<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-24 01:18:54
 * @Last Modified by:   AminBy
 * @Last Modified time: 2018-01-07 17:02:42
 */
namespace ScalersTalk\Setting;

class Config {
    static $SETTINGS = [
        'groups' => [
            'kuang' => '狂练2016',
            'ling' => '零基础2016',
            'kuang17' => '狂练2017',
            'ling17' => '零基础2017',
            'ling003' => '零阶训练营3期',
            'kuang003' => '狂阶训练营3期',
            'ling004' => '零阶训练营4期',
            'kuang004' => '狂阶训练营4期',
            'ling005' => '零阶训练营5期',
            'kuang005' => '狂阶训练营5期',
            'ling006' => '零阶训练营6期',
            'kuang006' => '狂阶训练营6期',
            'nce3' => '新概念3',
            'nce2' => '新概念2',
            'test' => '用于测试',
        ],
        'users' => [
            'crazy' => [
                'pass' => 'apple',
                'groups' => [
                    'kuang17',
                    'kuang003',
                    // 'kuang004',
                    // 'kuang005',
                    // 'kuang006',
                ],
            ],
            'zero' => [
                'pass' => 'pear',
                'groups' => [
                    'ling17',
                    'ling003',
                    // 'ling004',
                    // 'ling005',
                    // 'ling006',
                ],
            ],
            'nce3' => [
                'pass' => 'durian',
                'groups' => [
                    'nce3',
                ],
            ],
            'nce2' => [
                'pass' => 'durian',
                'groups' => [
                    'nce2',
                ],
            ],
            'manager' => [
                'pass' => 'banana',
                'groups' => [
                    'kuang17',
                    'ling17',
                    'ling003',
                    'kuang003',
                    // 'ling004',
                    // 'kuang004',
                    // 'ling005',
                    // 'kuang005',
                    // 'ling006',
                    // 'kuang006',
                    'test',
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
