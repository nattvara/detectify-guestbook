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
     * @var string variable that starts a variable name in a route path
     */
    const VARIABLE_CHARACTER = '$';

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
     * Check if route contains any variables
     *
     * @return boolean
     */
    public function hasVariables(): bool {
        if (strpos($this->path, '$') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Try to synthesize a path
     *
     * Overwrite the values inside the given
     * path with the values of this path
     * at variable locations
     *
     * @param  string $inputPath
     * @return string
     */
    public function synthesize(string $inputPath): string {
        $inputPath  = explode('/', $inputPath);
        $basePath   = explode('/', $this->path);
        foreach ($basePath as $placement => $part) {
            if ($inputPath[$placement] === $part) {
                continue;
            }
            if (!isset($inputPath[$placement])) {
                break;
            }
            if (strpos($part, Route::VARIABLE_CHARACTER) !== false) {
                $inputPath[$placement] = $part;
            }
        }
        return implode('/', $inputPath);
    }

    /**
     * Parse url varaibles from request
     *
     * @param  Request $request
     * @return array
     */
    public function parseUrlVariables(Request $request): array {
        $inputPath  = explode('/', $request->getPath());
        $basePath   = explode('/', $this->path);
        $data       = [];
        foreach ($basePath as $placement => $part) {
            if (strpos($part, Route::VARIABLE_CHARACTER) !== false) {
                $name   = str_replace(Route::VARIABLE_CHARACTER, '', $part);
                $value  = $inputPath[$placement];
                $data[$name] = $value;
            }
        }
        return $data;
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
