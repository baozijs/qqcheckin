<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-24 01:07:49
 * @Last Modified by:   AminBy
 * @Last Modified time: 2017-02-03 01:19:52
 */
namespace ScalersTalk\Setting;

class Items {
    const ITEMS17 = [
        'dictate' => [
            'name' => '听写',
            'valid' => '图片',
        ],
        'dictate1' => [
            'name' => '自选1',
            'valid' => '图片',
        ],
        'dictate2' => [
            'name' => '自选2',
            'valid' => '图片',
        ],
        'dictate3' => [
            'name' => '自选3',
            'valid' => '图片',
        ],
        'note' => [
            'name' => '笔记',
            'valid' => '图片',
        ],
        'read' => [
            'name' => '朗读',
            'valid' => '语音',
        ],
        'revise' => [
            'name' => '复习',
            'valid' => false,
        ],
        'review' => [
            'name' => '周复盘',
            'valid' => false,
        ],
    ];
    static $ITEMS = [
        'kuang' => [
            'dictate' => [
                'name' => '听写',
                'valid' => '图片',
            ],
            'note' => [
                'name' => '笔记',
                'valid' => '图片',
                ],
            'read' => [
                'name' => '朗读',
                'valid' => '语音',
                ],
            'write' => [
                'name' => '造句',
                'valid' => false,
                ],
            'recite' => [
                'name' => '复述',
                'valid' => '语音',
                ],
            'review' => [
                'name' => '周复盘',
                'valid' => false,
                ],
        ],
        'ling' => [
            'dictate' => [
                'name' => '听写',
                'valid' => '图片',
            ],
            'note' => [
                'name' => '笔记',
                'valid' => '图片',
            ],
            'review' => [
                'name' => '周复盘',
                'valid' => false,
            ],
        ],
        "kuang17" => self::ITEMS17,
        "ling17" => self::ITEMS17,
        "train001" => self::ITEMS17,
        "test" => self::ITEMS17,
    ];

    public static function get($group) {
        return empty(self::$ITEMS[$group]) ? [] : self::$ITEMS[$group];
    }
}