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
use \ScalersTalk\Setting\Config;

define('DEBUG', true);

$app = new \Slim\App();
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

$app->get('/', function (Request $req, Response $resp) {
    return $this->view->render($resp, "index.twig", ['groups' => Config::get('groups')]);
});

$app->get('/admin/{group}/view', function(Request $req, Response $resp, $args) {
    $admin = new ModAdmin($this);
    $auth = new ModAuth($this);

    $auth->needAdmin($req, $resp, $args);
    $admin->viewAll($req, $resp, $args);
});


$app->post('/admin/{group}/upload', function(Request $req, Response $resp, $args) {
    $admin = new ModAdmin($this);
    $auth = new ModAuth($this);

    $auth->needAdmin($req, $resp, $args);
    $admin->upload($req, $resp, $args);
});


$app->get('/admin/{group}/upload', function(Request $req, Response $resp, $args) {
    $admin = new ModAdmin($this);
    $auth = new ModAuth($this);
    $auth->needAdmin($req, $resp, $args);
    $admin->showUpload($req, $resp, $args);
});


$app->get('/user/{group}/{qqno}', function(Request $req, Response $resp, $args) {
    $user = new ModUser($this);
    $user->viewByQqno($req, $resp, $args);
});

$app->run();

