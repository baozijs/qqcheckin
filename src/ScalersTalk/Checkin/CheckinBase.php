<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-16 17:21:02
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-16 18:04:48
 */
namespace ScalersTalk\Checkin;

abstract class CheckinBase {
    protected $app;
    public function __construct($app) {
        $this->app = $app;
    }
}