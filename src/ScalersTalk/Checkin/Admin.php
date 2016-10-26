<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-16 16:50:10
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-26 09:05:04
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
        $this->app->view->bind('groups', Config::get('groups'));
        $this->app->view->render($resp, "upload.phtml", $args);
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

        // print_r($chatParser->checkins);
        // print_r($chatParser->leaves);
        $dataCheckin = new DataCheckin($args['group']);
        $dataLeave = new DataLeave($args['group']);
        $dataQQuser = new DataQQUser($args['group']);

        // print_r($chatParser->getQqusers());die;
        $dataLeave->batch_save($chatParser->leaves);
        $dataCheckin->batch_save($chatParser->checkins);
        $dataQQuser->batch_save($chatParser->getQqusers());

    }

    public function showAdmin(Request $req, Response $resp, $args) {
        $args['groups'] = Config::get('groups');
        $this->app->view->render($resp, "admin.phtml", $args);
    }
}