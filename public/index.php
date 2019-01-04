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
use Guestbook\Http\Responses\CssResponse;
use Guestbook\Http\Responses\HtmlResponse;
use Guestbook\Http\Responses\ImageResponse;
use Guestbook\Http\Responses\JavascriptResponse;
use Guestbook\Http\Router;
use Guestbook\Http\Routes\GET;
use Guestbook\Http\Routes\POST;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../.env.php');

$request = new Request;
$request->readPhpGlobals();

HtmlResponse::setResourceDirectory(__DIR__ . '/../resources/html');
JavascriptResponse::setResourceDirectory(__DIR__ . '/../dist');
CssResponse::setResourceDirectory(__DIR__ . '/../resources/css');
ImageResponse::setResourceDirectory(__DIR__ . '/../resources/img');

$router = new Router;
$router->registerRoutes([
    new GET('/login', \Guestbook\Http\Controllers\LoginController::class, 'viewLoginForm'),
    new POST('/login', \Guestbook\Http\Controllers\LoginController::class, 'login'),
    new POST('/logout', \Guestbook\Http\Controllers\LoginController::class, 'logout'),
    new GET('/register', \Guestbook\Http\Controllers\LoginController::class, 'viewRegisterForm'),
    new POST('/register', \Guestbook\Http\Controllers\LoginController::class, 'register'),
    new POST('/register/validate/email', \Guestbook\Http\Controllers\LoginController::class, 'validateEmail'),
    new POST('/register/validate/name', \Guestbook\Http\Controllers\LoginController::class, 'validateName'),
    new POST('/register/validate/password', \Guestbook\Http\Controllers\LoginController::class, 'validatePassword'),
    new POST('/register/validate/password_repeat', \Guestbook\Http\Controllers\LoginController::class, 'validatePasswordRepeat'),

    new GET('/me', \Guestbook\Http\Controllers\UserController::class, 'me'),

    new GET('/', \Guestbook\Http\Controllers\GuestbookController::class, 'index'),

    new GET('/main.js', \Guestbook\Http\Controllers\AssetController::class, 'js'),
    new GET('/main.css', \Guestbook\Http\Controllers\AssetController::class, 'css'),
    new GET('/logo.png', \Guestbook\Http\Controllers\AssetController::class, 'logo'),
]);

$response = $router->route($request);
$response->write();
