<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/cloud.php';
require __DIR__ . '/functions.php';
/*
 * A simple Slim based sample application
 *
 * See Slim documentation:
 * http://www.slimframework.com/docs/
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Views\PhpRenderer;
use \LeanCloud\Client;
use \LeanCloud\Storage\CookieStorage;
use \LeanCloud\Engine\SlimEngine;
use \LeanCloud\Query;
use \LeanCloud\Object;

use \ScalersTalk\Checkin\Auth as ModAuth;
use \ScalersTalk\Checkin\Admin as ModAdmin;
use \ScalersTalk\Checkin\User as ModUser;

define('DEBUG', true);

define('DEFAULT_START', "sun 1 week ago");
define('DEFAULT_END', "last sat");

session_start();
$app = new \Slim\App();
ModAuth::init($app);
ModAdmin::init($app);
ModUser::init($app);

// 禁用 Slim 默认的 handler，使得错误栈被日志捕捉
unset($app->getContainer()['errorHandler']);

Client::initialize(
    getenv("LC_APP_ID"),
    getenv("LC_APP_KEY"),
    getenv("LC_APP_MASTER_KEY")
);
// 将 sessionToken 持久化到 cookie 中，以支持多实例共享会话
Client::setStorage(new CookieStorage());

SlimEngine::enableHttpsRedirect();
$app->add(new SlimEngine());

// 使用 Slim/PHP-View 作为模版引擎
$container = $app->getContainer();
// $container["view"] = function($container) {
//     return new \Slim\Views\PhpRenderer(__DIR__ . "/views/");
// };
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig(__DIR__ . "/twigs/", [
        // 'cache' => __DIR__ . '/../cache'
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));

    return $view;
};

$app->add(function ($request, $response, $next) {
    ModAuth::inst()->setIfAdminForView();
    $response = $next($request, $response);
    // $response->getBody()->write('AFTER');
    return $response;
});


$app->get('/', function (Request $req, Response $resp) {
    return $this->view->render($resp, "index.twig", ['groups' => ModAuth::inst()->getGroups()]);
})->setName('home');

$app->get('/admin[/superadmin/{superadmin}]', function (Request $req, Response $resp, $args) {
    if($res = ModAuth::inst()->needAdmin($req, $resp, $args)) {
        return $res;
    }
    return ModAdmin::inst()->showAdmin($req, $resp, $args);
})->setName('admin-home');

$app->post('/login', function (Request $req, Response $resp, $args) {
    return ModAuth::inst()->login($req, $resp, $args);
});

$app->get('/login', function (Request $req, Response $resp, $args) {
    return $this->view->render($resp, "login.twig", $args);
})->setName('auth-login');

$app->get('/logout', function (Request $req, Response $resp, $args) {
    return ModAuth::inst()->logout($req, $resp);
})->setName('auth-logout');

$app->get('/admin/{group}/view', function(Request $req, Response $resp, $args) {
    if($res = ModAuth::inst()->needAdmin($req, $resp, $args)) {
        return $res;
    }
    return ModAdmin::inst()->viewAll($req, $resp, $args);
})->setName('admin-view');

$app->get('/admin/{group}/statistics', function(Request $req, Response $resp, $args){

    if($res = ModAuth::inst()->needAdmin($req, $resp, $args)) {
        return $res;
    }
    return ModAdmin::inst()->viewStatistics($req, $resp, $args);
})->setName('admin-statistics');

$app->post('/admin/{group}/upload', function(Request $req, Response $resp, $args) {

    if($res = ModAuth::inst()->needAdmin($req, $resp, $args)) {
        return $res;
    }
    return ModAdmin::inst()->upload($req, $resp, $args);
});


$app->get('/admin/{group}/upload', function(Request $req, Response $resp, $args) {
    if($res = ModAuth::inst()->needAdmin($req, $resp, $args)) {
        return $res;
    }
    return ModAdmin::inst()->showUpload($req, $resp, $args);
})->setName('admin-upload');

$app->get('/admin/{group}/member', function(Request $req, Response $resp, $args) {
    if ($res = ModAuth::inst()->needAdmin($req, $resp, $args)) {
        return $res;
    }
    return ModAdmin::inst()->viewUsers($req, $resp, $args);
})->setName('user-manage');

$app->delete('/ajax/admin/{group}/member/{qqno}', function(Request $req, Response $resp, $args) {
    if ($res = ModAuth::inst()->ajaxNeedAdmin($req, $resp, $args)) {
        return $res;
    }
    $resp = ModAdmin::inst()->deleteUser($req, $resp, $args);
    return $resp;
});

$app->get('/user/{group}[/{qqno}]', function(Request $req, Response $resp, $args) {
    return ModUser::inst()->viewByQqno($req, $resp, $args);
})->setName('user-view');

$app->get('/ajax/admin/{group}/clean', function(Request $req, Response $resp, $args) {
    if ($res = ModAuth::inst()->ajaxNeedAdmin($req, $resp, $args)) {
        return $res;
    }
    $resp = ModAdmin::inst()->cleanData($req, $resp, $args);
    return $resp;
});

$app->run();

