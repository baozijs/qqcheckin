<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-16 16:50:10
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-27 18:29:06
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
use \ScalersTalk\Util\ChatParser;
use \ScalersTalk\Setting\Items;
use \ScalersTalk\Setting\Config;

class Admin extends CheckinBase {

    public function showUpload(Request $req, Response $resp, $args) {
        $this->app->view['groups'] = Config::get('groups');
        $this->app->view->render($resp, "upload.twig", $args);
    }

    public function upload(Request $req, Response $resp, $args) {
        $groups = Config::get('groups');

        $files = $req->getUploadedFiles();
        if(empty($files['qqchat'])) {
            die('file qqchat is empty!');
        }
        if(empty($args['group']) || !in_array($args['group'], array_keys($groups))) {
            die('illegal group value!');
        }
        $tmpfile = $files['qqchat']->file;
        $chatParser = new ChatParser($tmpfile, Items::get($args['group']), 0);
        $chatParser->parse();

        $dataCheckin = new DataCheckin($args['group']);
        $dataLeave = new DataLeave($args['group']);
        $dataQQuser = new DataQQUser($args['group']);

        $dataLeave->batch_save($chatParser->leaves);
        $dataCheckin->batch_save($chatParser->checkins);
        $dataQQuser->batch_save($chatParser->getQqusers());
    }

    public function viewAll(Request $req, Response $resp, $args) {
        $dataCheckin = new DataCheckin($args['group']);
        $dataLeave = new DataLeave($args['group']);
        $dataQQUser = new DataQQUser($args['group']);

        $_qqusers = DataQQUser::asArray($dataQQUser->all());
        if(empty($_qqusers)) {
            die('qq users is empty');
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

        $_leaves = DataLeave::asArray($dataLeave->allWithDate($start, $end));
        $_checkins = DataCheckin::asArray($dataCheckin->allWithDate($start, $end));

        $_qqusers = array_column($_qqusers, 'nick', 'qqno');
        $_leaves = \array_group_by($_leaves, 'qqno', 'date');
        $_checkins = \array_group_by($_checkins, 'qqno', 'date');

        $args['_range'] = range($start, $end, 86400);

        $args['_checkins'] = $_checkins;
        $args['_leaves'] = $_leaves;
        $args['_qqusers'] = $_qqusers;

        $this->app->view->render($resp, "all-records.twig", $args);
    }

    public function showAdmin(Request $req, Response $resp, $args) {
        $args['groups'] = Config::get('groups');
        $this->app->view->render($resp, "admin.twig", $args);
    }
}