<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-16 16:53:10
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-27 18:30:23
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
use \ScalersTalk\Setting\Items;
use \ScalersTalk\Setting\Config;

class User extends CheckinBase {

    // function 
    function viewByQqno(Request $req, Response $resp, $args) {
        $dataCheckin = new DataCheckin($args['group']);
        $dataLeave = new DataLeave($args['group']);
        $dataQQUser = new DataQQUser($args['group']);

        $qqno = $args['qqno']; // '547096523';

        $_nick = $dataQQUser->nick($qqno);
        if(empty($_nick)) {
            die("user {$qqno} has no records.");
        }

        $query = $req->getQueryParams();
        if(empty($query['dateRange'])) {
            $start = "sun last week";
            $end = "sat this week";
        }
        else {
            list($start, $end) = explode(' to ', $query['dateRange']);
        }
        $start = strtotime($start);
        $end = strtotime($end);

        $args += compact('start', 'end');

        $_leaves = DataLeave::asArray($dataLeave->singleWithDate($qqno, $start, $end));
        $_leaves = \array_group_by($_leaves, 'date');

        $_checkins = DataCheckin::asArray($dataCheckin->singleWithDate($qqno, $start, $end));
        $_checkins = \array_group_by($_checkins, 'date');

        // print_r($_checkins); die;

        // $mindate = min(array_merge(array_keys($_checkins), array_keys($_leaves), [strtotime('today')]));
        // $maxdate = max(array_merge(array_keys($_checkins), array_keys($_leaves), [strtotime('today')]));

        $args['_range'] = range($end, $start, 86400);
        $args['_checkins'] = $_checkins;
        $args['_leaves'] = $_leaves;
        $args['_nick'] = $_nick;

        $this->app->view->render($resp, "user-records.twig", $args);
    }
}