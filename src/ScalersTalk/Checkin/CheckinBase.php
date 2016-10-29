<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-16 17:21:02
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-30 01:50:55
 */
namespace ScalersTalk\Checkin;
use \ScalersTalk\Data\Config as DataConfig;

abstract class CheckinBase {
    protected $app;
    public function __construct($app) {
        $this->app = $app;
    }

    public function setLastUpdatedForView($group) {
        $dataConfig = new DataConfig($group);
        $this->app->view['lastUpdated'] = $dataConfig->get('lastUpdated', 0);
    }
}