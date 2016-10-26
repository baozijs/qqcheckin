<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-24 01:07:49
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-24 01:24:28
 */
namespace ScalersTalk\Setting;

class Items {
    private const ITEMS = [
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
        ]
    ];

    public static function get($group) {
        return empty(self::ITEMS[$group]) ? [] : self::ITEMS[$group];
    }
}