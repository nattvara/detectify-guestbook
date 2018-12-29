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

require_once(__DIR__ . '/../vendor/autoload.php');

$request = new Request;
$request->readPhpGlobals();

HtmlResponse::setResourceDirectory(__DIR__ . '/../resources/html');
JavascriptResponse::setResourceDirectory(__DIR__ . '/../resources/js');
CssResponse::setResourceDirectory(__DIR__ . '/../resources/css');
ImageResponse::setResourceDirectory(__DIR__ . '/../resources/img');

$router = new Router;
$router->registerRoutes([
    new GET('/', \Guestbook\Http\Controllers\WelcomeController::class, 'index'),
    new GET('/now', \Guestbook\Http\Controllers\WelcomeController::class, 'now'),
    new GET('/app.js', \Guestbook\Http\Controllers\AssetController::class, 'js'),
    new GET('/app.css', \Guestbook\Http\Controllers\AssetController::class, 'css'),
    new GET('/logo.png', \Guestbook\Http\Controllers\AssetController::class, 'logo'),
]);

$response = $router->route($request);
$response->write();
