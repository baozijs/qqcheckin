<?php
/**
 * @Author: AminBy
 * @Date:   2016-10-16 16:50:30
 * @Last Modified by:   AminBy
 * @Last Modified time: 2016-10-27 17:01:00
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

class Auth extends CheckinBase {

    function needAdmin() {}
}