<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Guestbook\Http\Request;
use Guestbook\Http\Router;
use Guestbook\Http\Routes\GET;

require_once(__DIR__ . '/../vendor/autoload.php');

$request = new Request;
$request->readPhpGlobals();

$router = new Router;
$router->registerRoutes([
    new GET('/', \Guestbook\Http\Controllers\WelcomeController::class, 'index'),
    new GET('/greetings/sir', \Guestbook\Http\Controllers\WelcomeController::class, 'sir'),
    new GET('/greetings/lady', \Guestbook\Http\Controllers\WelcomeController::class, 'lady'),
]);

$response = $router->route($request);
$response->write();
