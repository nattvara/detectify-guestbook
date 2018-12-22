<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Guestbook\Http\Routes;

use Guestbook\Http\Controllers\Controller;
use Guestbook\Http\Exceptions\HttpException;
use Guestbook\Http\Request;
use Guestbook\Http\Responses\Response;

abstract class Route {

    /**
     * Path of route
     *
     * @var string
     */
    protected $path = '';

    /**
     * Controller for route
     *
     * @var Controller
     */
    protected $controller;

    /**
     * Name of method to call on controller
     *
     * @var string
     */
    protected $method;

    /**
     * New route
     *
     * @param string     $path
     * @param string     $controller controller class
     * @param string     $method     method to call
     */
    public function __construct(string $path, string $controller, string $method) {
        $this->path         = $path;
        $this->controller   = new $controller;
        $this->method       = $method;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }

    /**
     * Execute method on controller tied to this request
     *
     * @param  Request $request
     * @return Response
     */
    public function execute(Request $request): Response {
        if (!method_exists($this->controller, $this->method)) {
            throw new HttpException(sprintf('Controller does not have a method called \'%s\'', $this->method));
        }
        return $this->controller->{$this->method}($request);
    }

}
