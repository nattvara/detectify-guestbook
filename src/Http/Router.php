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
            if ($route->getPath() === $path) {
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
            return $route->execute($request);
        } catch (RouteNotFoundException $e) {
            return (new HtmlResponse('404.html'))->withStatusCode(404);
        } catch (UnauthenticatedException $e) {
            return new RedirectResponse('/login');
        } catch (GuestException $e) {
            return new RedirectResponse('/me');
        } catch (InvalidCsrfException $e) {
            return new HtmlResponse('error.html', ['reason' => 'Invalid CSRF Token']);
        }
    }

}
