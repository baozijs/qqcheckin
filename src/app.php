<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/cloud.php';

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

use \ScalersTalk\Checkin\Auth;
use \ScalersTalk\Checkin\Admin;
use \ScalersTalk\Checkin\User;
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
$container["view"] = function($container) {
    return new \Slim\Views\PhpRenderer(__DIR__ . "/views/");
};

$auth = new Auth($app);
$user = new User($app);

$app->get('/', function (Request $req, Response $resp) {
    return $this->view->render($resp, "index.phtml", ['groups' => Config::get('groups')]);
});

$app->get('/admin[/{group}]', function(Request $req, Response $resp, $args) {
    $admin = new Admin($this);
    // $auth->needAdmin($req, $resp, $args);
    $admin->showAdmin($req, $resp, $args);
});


$app->post('/admin/{group}/upload', function(Request $req, Response $resp, $args) {
    $admin = new Admin($this);
    // var_dump($files);
    // $auth->needAdmin($req, $resp, $args);
    $admin->upload($req, $resp, $args);
});


$app->get('/admin/{group}/upload', function(Request $req, Response $resp, $args) {
    $admin = new Admin($this);
    // var_dump($files);
    // $auth->needAdmin($req, $resp, $args);
    $admin->showUpload($req, $resp, $args);
});

// 显示 todo 列表
$app->get('/todos', function(Request $req, Response $resp) {
    $query = new Query("Todo");
    $query->descend("createdAt");
    try {
        $todos = $query->find();
    } catch (\Exception $ex) {
        error_log("Query todo failed!");
        $todos = array();
    }
    return $this->view->render($resp, "todos.phtml", array(
        "title" => "TODO 列表",
        "todos" => $todos,
    ));
});

$app->post("/todos", function(Request $req, Response $resp) {
    $data = $req->getParsedBody();
    $todo = new Object("Todo");
    $todo->set("content", $data["content"]);
    $todo->set("added", time());
    $todo->save();
    return $resp->withStatus(302)->withHeader("Location", "/todos");
});

$app->get('/hello/{name}', function (Request $req, Response $resp) {
    $name = $req->getAttribute('name');
    $resp->getBody()->write("Hello, $name");

    return $resp;
});

$app->run();

