<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Guestbook\Http\Controllers;

use Guestbook\Http\Request;
use Guestbook\Http\Responses\HtmlResponse;
use Guestbook\Http\Responses\PlainTextResponse;

class LoginController extends Controller {

    /**
     * View the login form
     *
     * @param  Request $request
     * @return HtmlResponse
     */
    public function viewLoginForm(Request $request): HtmlResponse {
        return new HtmlResponse('login.html');
    }

    /**
     * View the register form
     *
     * @param  Request $request
     * @return HtmlResponse
     */
    public function viewRegisterForm(Request $request): HtmlResponse {
        return (new HtmlResponse('register.html'))->withCsrfToken($request);
    }

    /**
     * Register a user
     *
     * @param  Request $request
     * @return HtmlResponse
     */
    public function register(Request $request): HtmlResponse {
        $this->validateCsrf($request);
        return new HtmlResponse('registered.html');
    }
}
