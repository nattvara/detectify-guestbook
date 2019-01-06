<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Guestbook\Http;

use Guestbook\Http\Controllers\Exceptions\InvalidCsrfException;
use Guestbook\Http\Exceptions\GuestException;
use Guestbook\Http\Exceptions\RouteNotFoundException;
use Guestbook\Http\Exceptions\UnauthenticatedException;
use Guestbook\Http\Responses\HtmlResponse;
use Guestbook\Http\Responses\JsonResponse;
use Guestbook\Http\Responses\RedirectResponse;
use Guestbook\Http\Responses\Response;
use Guestbook\Http\Routes\Route;

class Router {

    /**
     * Registered routes
     *
     * @var array
     */
    private $routes = [];

    /**
     * Register route
     *
     * @param Route $route
     * @return void
     */
    public function registerRoute(Route $route) {
        $this->routes[] = $route;
    }

    /**
     * Register array of routes
     *
     * @param  array  $routes
     * @return void
     */
    public function registerRoutes(array $routes) {
        foreach ($routes as $route) {
            if (!($route instanceof Route)) {
                throw new RouterException('invalid route');
            }
            $this->registerRoute($route);
        }
    }

    /**
     * Retrieve route
     *
     * @param  string $method name of class
     * @param  string $path   eg /foo/bar
     * @return Route
     */
    public function retrieveRoute($method, string $path): Route {
        foreach ($this->routes as $route) {
            if (!($route instanceof $method)) {
                continue;
            }

            if ($route->getPath() === $path && !$route->hasVariables()) {
                return $route;
            }

            if ($route->getPath() === $route->synthesize($path) && $route->hasVariables()) {
                return $route;
            }
        }
        throw new RouteNotFoundException(sprintf('route was not found at \'%s\'', $path));
    }

    /**
     * Route request
     *
     * @param  Request $request
     * @return Response
     */
    public function route(Request $request): Response {
        try {
            $route = $this->retrieveRoute($request->getMethod(), $request->getPath());
            if ($route->hasVariables()) {
                $request->setUrlVariables($route->parseUrlVariables($request));
            }
            return $route->execute($request);
        } catch (RouteNotFoundException $e) {
            if ($request->isJson()) {
                return (new JsonResponse([
                    'status_code'   => 404,
                    'message'       => 'Resource not found',
                    'errors'        => []
                ]))->withStatusCode(404);
            }
            return (new HtmlResponse('404.html'))->withStatusCode(404);
        } catch (UnauthenticatedException $e) {
            if ($request->isJson()) {
                return (new JsonResponse([
                    'status_code'   => 403,
                    'message'       => 'You need to login to view this resource',
                    'errors'        => []
                ]))->withStatusCode(403);
            }
            return (new HtmlResponse('error.html', ['reason' => 'You need to login to view this resource']))->withStatusCode(403);
        } catch (GuestException $e) {
            return new RedirectResponse('/');
        } catch (InvalidCsrfException $e) {
            if ($request->isJson()) {
                return (new JsonResponse([
                    'status_code'   => 500,
                    'message'       => 'Invalid CSRF Token',
                    'errors'        => []
                ]))->withStatusCode(500);
            }
            return (new HtmlResponse('error.html', ['reason' => 'Invalid CSRF Token']))->withStatusCode(500);
        }
    }

}
