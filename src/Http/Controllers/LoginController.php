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

        $this->addValidationRule($request, 'email', 'required');
        $this->addValidationRule($request, 'email', 'email');
        $this->addValidationRule($request, 'email', 'length', ['min' => 5, 'max' => 128]);
        $this->addValidationRule($request, 'name', 'required');
        $this->addValidationRule($request, 'name', 'length', ['min' => 2, 'max' => 128]);
        $this->addValidationRule($request, 'password', 'required');
        $this->addValidationRule($request, 'password', 'length', ['min' => 2]);
        $this->addValidationRule($request, 'password', 'pwnedpasswords.com');
        $this->addValidationRule($request, 'password_repeat', 'required', [], 'repeat password');
        $this->addValidationRule($request, 'password_repeat', 'match', ['key' => 'password', 'key_pretty' => 'Password'], 'repeat password');

        if (!$this->validateRequest($request)) {
            return (new HtmlResponse('register.html', [
                'email' => $request->input('email') ? $request->input('email') : '',
                'name'  => $request->input('name') ? $request->input('name') : '',
            ]))->withCsrfToken($request)
            ->withHtmlVariables([
                'errors' => $this->formatErrorsAsHtml($this->errors)
            ]);
        }

        return new HtmlResponse('registered.html');
    }
}
