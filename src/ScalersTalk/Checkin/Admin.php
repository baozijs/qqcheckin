<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-16 16:50:10
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-16 18:33:50
 */
namespace ScalersTalk\Checkin;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Views\PhpRenderer;
use \LeanCloud\Client;
use \LeanCloud\Storage\CookieStorage;
use \LeanCloud\Engine\SlimEngine;
use \LeanCloud\Query;
use \LeanCloud\Object;

use \ScalersTalk\Util\ChatParser;

class Admin extends CheckinBase {


    public function upload(Request $req, Response $resp, $args) {
        $files = $req->getUploadedFiles();
        if(empty($files['qqchat'])) {
            die('file qqchat is empty!');
        }
        if(empty($args['group']) || !in_array($args['group'], ['kuang', 'ling'])) {
            die('illegal group value!');
        }
        $file = $files['qqchat'];
        $chatParser = new ChatParser($file['file']);
    }

    public function showAdmin(Request $req, Response $resp, $args) {
        $this->app->view->render($resp, "admin.phtml", $args);
    }
}