<?php

/**
 * @Author: AminBy
 * @Date:   2018-02-16 19:19:24
 * @Last Modified by:   AminBy
 * @Last Modified time: 2018-02-21 22:55:00
 */
namespace ScalersTalk\Checkin;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \ScalersTalk\Data\Manager as DataManager;
use \ScalersTalk\Data\Group as DataGroup;
use \ScalersTalk\Data\GroupItem as DataGroupItem;

class System extends CheckinBase {
    private $dataGroup;
    private $dataGroupItem;
    private $dataManager;

    public function __construct($app) {
        parent::__construct($app);
        $this->dataGroup = new DataGroup;
        $this->dataGroupItem = new DataGroupItem;
        $this->dataManager = new DataManager;
    }

    function showGroups(Request $req, Response $resp, $args) {
        $args['groups'] = $this->dataGroup->queryAllAsArray(true);

        $view = $this->app->getContainer()['view'];
        return $view->render($resp, "manage-groups.twig", $args);
    }

    function saveGroup(Request $req, Response $resp, $args) {
        $group = $req->getParsedBody();
        if (empty($group['gid'])) {
            $ret = [
                'ok' => false,
                'msg' => 'gid mustn\'t be empty'
            ];
        }
        elseif (empty($group['gname'])) {
            $ret = [
                'ok' => false,
                'msg' => 'gname mustn\'t be empty'
            ];
        }
        else {
            $ok = $this->dataGroup->saveGroup($req->getParsedBody());

            $ret = ['ok' => $ok];
            $ret['msg'] = $ok ? 'success' : 'error';
            $ret['data'] = $group;
        }

        return $resp->withStatus(200)->write(json_encode($ret));
    }

    function showManagers(Request $req, Response $resp, $args) {
        $args['managers'] = $this->dataManager->loadUsersAsArray();
        $args['groups'] = $this->dataGroup->queryAllAsArray(true);

        $mgr_groups = [];
        foreach($args['groups'] as $group) {
            foreach($group['managers'] as $mgr) {
                isset($mgr_groups[$mgr]) || $mgr_groups[$mgr] = [];
                $mgr_groups[$mgr][] = $group['gid'];
            }
        }
        foreach($args['managers'] as &$manager) {
            $manager['groups'] = isset($mgr_groups[$manager['name']]) 
            ? $mgr_groups[$manager['name']] : [];
        }

        $view = $this->app->getContainer()['view'];
        return $view->render($resp, "manage-managers.twig", $args);
    }

    function saveManagerGroup(Request $req, Response $resp, $args) {
        $isAdd = $args['operation'] == 'add';
        $data = $req->getParsedBody();
        if (empty($data['username']) || empty($data['gid'])) {
            $ret = [
                'ok' => false,
                'msg' => 'username or gid mustn\'t be empty'
            ];
        }
        else {
            $ok = $this->dataGroup->saveGroupManager($isAdd, $data['gid'], $data['username']);
            $ret = ['ok' => $ok];
            $ret['msg'] = $ok ? 'success' : 'error';
        }

        return $resp->withStatus(200)->write(json_encode($ret));
    }

    function saveManager(Request $req, Response $resp, $args) {
        $manager = $req->getParsedBody();
        if (empty($manager['name'])) {
            $ret = [
                'ok' => false,
                'msg' => 'name mustn\'t be empty'
            ];
        }
        else {
            $ok = $this->dataManager->saveManager($req->getParsedBody());

            $ret = ['ok' => $ok];
            $ret['msg'] = $ok ? 'success' : 'error';
            $ret['data'] = $manager;
        }

        return $resp->withStatus(200)->write(json_encode($ret));
    }

    function showGroupItems(Request $req, Response $resp, $args) {
        $args['grp'] = $this->dataGroup->getOneAsArray($args['gid']);
        $args['items'] = $this->dataGroupItem->fetchByGidAsArray($args['gid']);

        $view = $this->app->getContainer()['view'];
        return $view->render($resp, "manage-group-items.twig", $args);
    }

    function saveGroupItem(Request $req, Response $resp, $args) {
        $group = $req->getParsedBody();
        if (empty($group['key'])) {
            $ret = [
                'ok' => false,
                'msg' => 'key mustn\'t be empty'
            ];
        }
        elseif (empty($group['name'])) {
            $ret = [
                'ok' => false,
                'msg' => 'name mustn\'t be empty'
            ];
        }
        else {
            $ok = $this->dataGroupItem->saveGroupItem($args['gid'], $req->getParsedBody());

            $ret = ['ok' => $ok];
            $ret['msg'] = $ok ? 'success' : 'error';
            $ret['data'] = $group;
        }

        return $resp->withStatus(200)->write(json_encode($ret));
    }
}