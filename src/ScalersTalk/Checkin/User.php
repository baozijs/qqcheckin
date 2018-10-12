<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-16 16:53:10
 * @Last Modified by:   AminBy
 * @Last Modified time: 2018-10-12 23:42:19
 */
namespace ScalersTalk\Checkin;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Views\PhpRenderer;
use \LeanCloud\Client;
use \LeanCloud\Storage\CookieStorage;
use \LeanCloud\Engine\SlimEngine;

use \ScalersTalk\Data\Checkin as DataCheckin;
use \ScalersTalk\Data\Leave as DataLeave;
use \ScalersTalk\Data\QQUser as DataQQUser;

class User extends CheckinBase {
    const DEFAULT_END = DEFAULT_END;
    const DEFAULT_START = DEFAULT_START;

    // function 
    function viewByQqno(Request $req, Response $resp, $args) {
        $this->setLastUpdatedForView($args['group']);

        $view = $this->app->getContainer()['view'];
        if(empty($args['qqno'])) {
            $args['end'] = strtotime(self::DEFAULT_END);
            $args['start'] = strtotime(self::DEFAULT_START);
            return $view->render($resp, "user-index.twig", $args);
        }

        $dataCheckin = new DataCheckin($args['group']);
        $dataLeave = new DataLeave($args['group']);
        $dataQQUser = new DataQQUser($args['group']);

        // 会员信息
        $qqno = $args['qqno'];
        $_nick = $dataQQUser->nick($qqno);
        if(empty($_nick)) {
            die("未找到与QQ号'{$qqno}'相关的打卡记录.");
        }

        // 起止时间
        $query = $req->getQueryParams();
        if(empty($query['dateRange'])) {
            list($start, $end) = [self::DEFAULT_START, self::DEFAULT_END];
        }
        else {
            list($start, $end) = explode(' to ', $query['dateRange']);
        }
        list($start, $end) = [strtotime($start), strtotime($end)];

        $args += compact('start', 'end');

        // 获了数据
        $_leaves = DataLeave::asArray($dataLeave->singleWithDate($qqno, $start, $end));
        $_leaves = \array_group_by($_leaves, 'date');

        $_checkins = DataCheckin::asArray($dataCheckin->singleWithDate($qqno, $start, $end));
        $_checkins = \array_group_by($_checkins, 'date');

        // 绑定
        $args['_range'] = range($end, $start, 86400);
        $args['_checkins'] = $_checkins;
        $args['_leaves'] = $_leaves;
        $args['_nick'] = $_nick;

        return $view->render($resp, "user-records.twig", $args);
    }
}