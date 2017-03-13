<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-16 17:21:02
 * @Last Modified by:   AminBy
 * @Last Modified time: 2017-03-12 23:44:20
 */
namespace ScalersTalk\Checkin;
use \ScalersTalk\Data\Config as DataConfig;

abstract class CheckinBase {
    protected $app;
    protected function __construct($app) {
        $this->app = $app;
    }

    public function setLastUpdatedForView($group) {
        $dataConfig = new DataConfig($group);
        $view = $this->app->getContainer()['view'];
        $view['lastUpdated'] = $dataConfig->get('lastUpdated', 0);
        $view['urlRule'] = $dataConfig->get('urlRule', 'https://www.zybuluo.com/AminBy/note/542819');
    }

    private static $objs = [];
    public static function init($app) {
        $klass = get_called_class();
        if (!isset(self::$objs[$klass])) {
            self::$objs[$klass] = new $klass($app);
        }
        return self::$objs[$klass];
    }
    public static function inst() {
        $klass = get_called_class();
        if (!isset(self::$objs[$klass])) {
            die("$klass had not been initialized before!");
        }
        return self::$objs[$klass];
    }
}