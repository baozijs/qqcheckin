<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-16 16:50:30
 * @Last Modified by:   AminBy
 * @Last Modified time: 2018-02-16 08:57:22
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
use \ScalersTalk\Data\Group as DataGroup;
use \ScalersTalk\Data\Manager as DataManager;

use \ScalersTalk\Setting\Config;

class Auth extends CheckinBase {
    const KEY = '_CHECKINUSER';
    public function needAdmin(Request $req, Response $resp, $args) {
        if( !isset($_SESSION[self::KEY]['type'])
            || $_SESSION[self::KEY]['type'] != 'admin') {
            $router = $this->app->getContainer()['router'];
            return $resp->withStatus(302)->withHeader('Location', $router->pathFor('auth-login'));
        }
        if (!empty($args['group']) && !array_key_exists($args['group'], $this->getGroups())) {
            die('Not enough power to do this!');
        }
    }

    public function ajaxNeedAdmin(Request $req, Response $resp, $args) {
        if( !isset($_SESSION[self::KEY]['type'])
            || $_SESSION[self::KEY]['type'] != 'admin') {
            return $resp->withStatus(200)->write('{"ok":false, "msg":"unauthencated"}');
        }
        if (!empty($args['group']) && !array_key_exists($args['group'], $this->getGroups())) {
            return $resp->withStatus(200)->write('{"ok":false, "msg":"unauthencated"}');
        }
    }

    public function logout(Request $req, Response $resp) {
        unset($_SESSION[self::KEY]);
        $router = $this->app->getContainer()['router'];
        return $resp->withStatus(302)->withHeader('Location', $router->pathFor('auth-login'));
    }

    public function login(Request $req, Response $resp, $args) {
        $body = $req->getParsedBody();
        extract($body);


        $dataManager = new DataManager();
        $manager = $dataManager->fetchAsArray($user, $pass);
        if ($manager != null) {
            $dataGroup = new DataGroup();

            $mgr = [
                'type' => 'admin',
                'user' => $user,
            ];
            // 超级管理员, 加载所有分组
            if ($manager['sadmin']) {
                $groups = $dataGroup->queryAllAsArray();
            }
            // 超级管理员, 只加载有权限的分组
            else {
                $groups = $dataGroup->queryByManagerAsArray($manager['name']);
            }
            $mgr['groups'] = array_column($groups, null, 'gid');

            $_SESSION[self::KEY] = $mgr;
            $router = $this->app->getContainer()['router'];
            return $resp->withStatus(302)->withHeader('Location', $router->pathFor('admin-home'));
        }
        else {
            echo "登录失败";
        }
    }

    public function getGroups() {
        // print_r($_SESSION[self::KEY]);
        if (isset($_SESSION[self::KEY]) && !isset($_SESSION[self::KEY]['groups'])) {
            $req = $this->app->getContainer()->get('request');
            $resp = $this->app->getContainer()->get('response');
            $this->logout($req, $resp);
        }

        return isset($_SESSION[self::KEY]) ? $_SESSION[self::KEY]['groups'] : [];
    }

    public function setIfAdminForView() {
        if(isset($_SESSION[self::KEY]['type']) && $_SESSION[self::KEY]['type'] == 'admin') {
            $view = $this->app->getContainer()['view'];
            $view['isadmin'] = true;
        }
    }
}