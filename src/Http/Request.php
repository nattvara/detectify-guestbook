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

use Guestbook\Http\Exceptions\HttpException;
use Guestbook\Http\Routes\GET;

class Request {

    /**
     * Request path
     *
     * @var string
     */
    private $path = '';

    /**
     * Http method
     *
     * Class name
     *
     * @var string
     */
    private $method;

    /**
     * Read request variables from php globals
     *
     * @return void
     */
    public function readPhpGlobals() {
        $supportedMethods = [
            'GET'   => GET::class,
        ];
        if (!in_array($_SERVER['REQUEST_METHOD'], array_keys($supportedMethods))) {
            throw new HttpException(sprintf('Unsupported method \'%s\'', $_SERVER['REQUEST_METHOD']));
        }
        $this->setMethod($supportedMethods[$_SERVER['REQUEST_METHOD']]);
        $this->setPath($_SERVER['REQUEST_URI']);
    }

    /**
     * Set path
     *
     * @param string $path
     * @return void
     */
    public function setPath(string $path) {
        $this->path = $path;
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
     * Set http method
     *
     * @param  string $method
     * @return void
     */
    public function setMethod($method) {
        $this->method = $method;
    }

    /**
     * Get http method
     *
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }
}
