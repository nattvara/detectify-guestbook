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
use Guestbook\Http\Exceptions\SessionVariableNotFoundException;
use Guestbook\Http\Exceptions\UnauthenticatedException;
use Guestbook\Http\Routes\GET;
use Guestbook\Http\Routes\POST;
use Guestbook\Http\Validation\Validation;
use Guestbook\Models\Cookie;
use Guestbook\Models\Exceptions\UserNotFoundException;
use Guestbook\Models\User;

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
     * Url variables
     *
     * @var array
     */
    private $urlVariables = [];

    /**
     * Url query params
     *
     * @var array
     */
    private $queryParams = [];

    /**
     * @var Validation
     */
    private $validation;

    /**
     * @var User
     */
    private $user;

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

        $this->setQueryParams($_GET);
        $this->setInput($_POST);
        if (strpos($this->headers('content-type'), 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->setInput($data);
            }
        }

        $this->readUserFromSessionIfItExists();
        if (!$this->hasAuthenticatedUser) {
            $this->readTokenFromCookiesIfExistsAndSignUserIn();
        }
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
     * Get method as string
     *
     * @return string
     */
    public function getMethodAsString(): string {
        if ($this->method === GET::class) {
            return 'GET';
        } else if ($this->method === POST::class) {
            return 'POST';
        }
        throw new \Exception('Unkown method exception');
    }

    /**
     * Get user agent
     *
     * @return string
     */
    public function getUserAgent(): string {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * Get ip address
     *
     * @return string
     */
    public function getIp(): string {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Set query params
     *
     * @param array $queryParams
     * @return void
     */
    public function setQueryParams(array $queryParams) {
        $this->queryParams = $queryParams;
    }

    /**
     * Get query params
     *
     * @param  bool|string $key if defined value with specified key is returned
     * @return mixed
     */
    public function getQueryParams($key = false) {
        if (!$key) {
            return $this->queryParams;
        }
        if (!isset($this->queryParams[$key])) {
            return null;
        }
        return $this->queryParams[$key];
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
     * Set url variables
     *
     * @param array $urlVariables
     * @return void
     */
    public function setUrlVariables(array $urlVariables) {
        $this->urlVariables = $urlVariables;
    }

    /**
     * Get the value of a url variable
     *
     * @param  string $name
     * @return string
     */
    public function urlVariable(string $name): string {
        if (!isset($this->urlVariables[$name])) {
            return null;
        }
        return $this->urlVariables[$name];
    }

    /**
     * Get header(s)
     *
     * @param  null|string $name fetch a specific header, returns null if it don't exist
     * @return array|string|null
     */
    public function headers(?string $name = null) {
        $headers = getallheaders();
        foreach ($headers as $key => $value) {
            unset($headers[$key]);
            $headers[strtolower($key)] = $value;
        }
        if (!$name) {
            return $headers;
        }
        if (in_array($name, array_keys($headers))) {
            return $headers[$name];
        }
        return null;
    }

    /**
     * Check if request content type is json
     *
     * @return boolean
     */
    public function isJson(): bool {
        if (strpos($this->headers('content-type'), 'application/json') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Add session variables
     *
     * @param string $name
     * @param mixed  $value
     * @return void
     */
    public function addSessionVariable(string $name, $value) {
        $_SESSION[$name] = $value;
    }

    /**
     * Get session variable
     *
     * @param  string $name
     * @return mixed
     */
    public function getSessionVariable(string $name) {
        if (!isset($_SESSION[$name])) {
            throw new SessionVariableNotFoundException(sprintf('did not find any session variable with the name \'%s\'', $name));
        }
        return $_SESSION[$name];
    }

    /**
     * Destroy session
     *
     * @return void
     */
    public function destroySession() {
        session_destroy();
        $time = time() - (10 * 365 * 24 * 60 * 60); // - 10 years
        setcookie(session_name(), '', $time);
    }

    /**
     * Add cookie to response
     *
     * @param Cookie $cookie
     */
    public function addCookie(Cookie $cookie) {
        $time = time() + (10 * 365 * 24 * 60 * 60); // 10 years
        setcookie('token', $cookie->getToken(), $time, null, null, false, true);
    }

    /**
     * Clear cookies
     *
     * @return void
     */
    public function clearCookies() {
        if (!isset($_COOKIE['token'])) {
            return;
        }
        $token = $_COOKIE['token'];
        Cookie::deactivateToken($token);
        $time = time() - (10 * 365 * 24 * 60 * 60); // - 10 years
        setcookie('token', '', $time);
    }

    /**
     * Read token from cookies if it exists and sign user in
     * @return void
     */
    private function readTokenFromCookiesIfExistsAndSignUserIn() {
        if (!isset($_COOKIE['token'])) {
            return;
        }
        $token  = $_COOKIE['token'];
        try {
            $user   = User::findByCookieToken($token);
            $user->signIn($this, false);
        } catch (UserNotFoundException $e) {
            $this->clearCookies();
        }
    }

    /**
     * Read user from session if user_id exists in the session
     *
     * @return void
     */
    private function readUserFromSessionIfItExists() {
        try {
            $this->user();
        } catch (UnauthenticatedException $e) {
            return;
        }
    }

    /**
     * Get authenticated user
     *
     * @return User
     */
    public function user(): User {
        if (!$this->user) {
            try {
                $this->user = User::findByPublicId($this->getSessionVariable('user_id'));
            } catch (SessionVariableNotFoundException $e) {
                throw new UnauthenticatedException('tried to use an authenticated user and found none in the request');
            }
        }
        return $this->user;
    }

    /**
     * Check if request has an authenticated user
     *
     * @return boolean
     */
    public function hasAuthenticatedUser(): bool {
        if (!$this->user) {
            return false;
        }
        return true;
    }

    /**
     * Generate csrf token
     *
     * @return void
     */
    public function generateCsrfToken() {
        $token = hash('sha256', bin2hex(random_bytes(10000)));
        $this->addSessionVariable('csrf_token', $token);
    }

    /**
     * Get CSRF token
     *
     * @return string
     */
    public function getCsrfToken(): string {
        return $this->getSessionVariable('csrf_token');
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
        if ($this->headers('x-csrf-token') === $this->getCsrfToken()) {
            return true;
        }
        return false;
    }
}
