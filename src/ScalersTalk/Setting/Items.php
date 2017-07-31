<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-24 01:07:49
 * @Last Modified by:   AminBy
 * @Last Modified time: 2017-07-31 21:46:18
 */
namespace ScalersTalk\Setting;

class Items {
    private static function getItems17() {
        return [
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
    }
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
        'nce3' => [
            'read' => [
                'name' => '朗读',
                'valid' => '语音',
            ],
            'recite' => [
                'name' => '复述',
                'valid' => '语音',
            ],
            'retell' => [
                'name' => '背诵',
                'valid' => false,
            ],
            'comment' => [
                'name' => '点评',
                'valid' => false,
            ],
            'review-weekly' => [
                'name' => '周复盘',
                'valid' => false,
            ],
            'review-group' => [
                'name' => '群复盘',
                'valid' => false,
            ],
            'review-monthly' => [
                'name' => '月复盘',
                'valid' => false,
            ],
        ],
        "kuang17" => 'ITEMS17',
        "ling17" => 'ITEMS17',
        'ling003' => 'ITEMS17',
        'kuang003' => 'ITEMS17',
        'ling004' => 'ITEMS17',
        'kuang004' => 'ITEMS17',
        'ling005' => 'ITEMS17',
        'kuang005' => 'ITEMS17',
        'ling006' => 'ITEMS17',
        'kuang006' => 'ITEMS17',
        "test" => 'ITEMS17',
    ];

    public static function get($group) {
        $ret = empty(self::$ITEMS[$group]) ? [] : self::$ITEMS[$group];
        return is_string($ret) ? self::getItems17() : $ret;
    }
}