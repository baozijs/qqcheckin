<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-16 16:50:30
 * @Last Modified by:   AminBy
 * @Last Modified time: 2017-03-15 08:13:33
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

        $users = Config::get('users');
        if (isset($users[$user])
            && isset($users[$user]['pass'])
            && isset($users[$user]['groups'])
            && $users[$user]['pass'] == $pass)
        {
            $groups = Config::get('groups');
            $grpkeys = $users[$user]['groups'];
            if ($grpkeys == 'ALL') {
                $grpkeys = array_keys($groups);
            }

            $_SESSION[self::KEY] = [
                'type' => 'admin',
                'user' => $user,
                'groups' => array_intersect_key($groups, array_flip($grpkeys))
            ];

            $router = $this->app->getContainer()['router'];
            return $resp->withStatus(302)->withHeader('Location', $router->pathFor('admin-home'));
        }
    }

    public function getGroups() {
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