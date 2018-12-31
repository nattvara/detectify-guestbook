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
use Guestbook\Http\Routes\POST;
use Guestbook\Http\Validation\Validation;

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
     * Request input data
     *
     * @var array
     */
    private $data = [];

    /**
     * @var Validation
     */
    private $validation;

    /**
     * New Request
     */
    public function __construct() {
        $this->validation = new Validation;
    }

    /**
     * Read request variables from php globals
     *
     * @return void
     */
    public function readPhpGlobals() {
        session_start();
        $supportedMethods = [
            'GET'   => GET::class,
            'POST'  => POST::class,
        ];
        if (!in_array($_SERVER['REQUEST_METHOD'], array_keys($supportedMethods))) {
            throw new HttpException(sprintf('Unsupported method \'%s\'', $_SERVER['REQUEST_METHOD']));
        }
        $this->setMethod($supportedMethods[$_SERVER['REQUEST_METHOD']]);
        $this->setPath($_SERVER['REQUEST_URI']);
        $this->setInput($_POST);
    }

    /**
     * Get validation
     *
     * @return Validation
     */
    public function getValidation(): Validation {
        return $this->validation;
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

    /**
     * Set input data
     *
     * @param array $data
     * @return void
     */
    public function setInput(array $data) {
        $this->data = $data;
    }

    /**
     * Get input data
     *
     * @param  bool|string $key if defined value with specified key is returned
     * @return mixed
     */
    public function input($key = false) {
        if (!$key) {
            return $this->data;
        }
        if (!isset($this->data[$key])) {
            return null;
        }
        return $this->data[$key];
    }

    /**
     * Generate csrf token
     *
     * @return void
     */
    public function generateCsrfToken() {
        $token = hash('sha256', bin2hex(random_bytes(10000)));
        $_SESSION['csrf_token'] = $token;
    }

    /**
     * Get CSRF token
     *
     * @return string
     */
    public function getCsrfToken(): string {
        return $_SESSION['csrf_token'];
    }

    /**
     * Check wether request contains a valid CSRF token
     *
     * @return bool
     */
    public function containsValidCsrfToken(): bool {
        if ($this->input('csrf_token') === $this->getCsrfToken()) {
            return true;
        }
        return false;
    }
}
