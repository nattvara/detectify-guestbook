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
use Guestbook\Http\Responses\RedirectResponse;
use Guestbook\Http\Responses\Response;
use Guestbook\Models\Exceptions\InvalidPasswordException;
use Guestbook\Models\Exceptions\UserNotFoundException;
use Guestbook\Models\User;

class LoginController extends Controller {

    /**
     * View the login form
     *
     * @param  Request $request
     * @return HtmlResponse
     */
    public function viewLoginForm(Request $request): HtmlResponse {
        $this->guest($request);
        return (new HtmlResponse('login.html'))->withCsrfToken($request);
    }

    /**
     * Login in a user
     *
     * @param  Request $request
     * @return RedirectResponse|HtmlResponse
     */
    public function login(Request $request): Response {

        $this->guest($request);
        $this->validateCsrf($request);

        $this->addValidationRule($request, 'email', 'required');
        $this->addValidationRule($request, 'email', 'email');
        $this->addValidationRule($request, 'password', 'required');

        if (!$this->validateRequest($request)) {
            return (new HtmlResponse('login.html', [
                'email' => $request->input('email') ? $request->input('email') : ''
            ]))->withCsrfToken($request)
            ->withHtmlVariables([
                'errors' => $this->formatErrorsAsHtml($this->errors)
            ]);
        }

        $credentials = true;
        try {
            $user = User::authenticate($request->input('email'), $request->input('password'));
        } catch (UserNotFoundException $e) {
            $credentials = false;
        } catch (InvalidPasswordException $e) {
            $credentials = false;
        }

        if (!$credentials) {
            return (new HtmlResponse('login.html', [
                'email' => $request->input('email') ? $request->input('email') : ''
            ]))->withCsrfToken($request)
            ->withHtmlVariables([
                'errors' => $this->formatErrorsAsHtml([
                    ['human_friendly' => 'Wrong email and/or password']
                ])
            ]);
        }

        $user->signIn($request);

        return new RedirectResponse('/me');
    }

    /**
     * View the register form
     *
     * @param  Request $request
     * @return HtmlResponse
     */
    public function viewRegisterForm(Request $request): HtmlResponse {
        $this->guest($request);
        return (new HtmlResponse('register.html'))->withCsrfToken($request);
    }

    /**
     * Register a user
     *
     * @param  Request $request
     * @return HtmlResponse
     */
    public function register(Request $request): HtmlResponse {

        $this->guest($request);
        $this->validateCsrf($request);

        $this->addValidationRule($request, 'email', 'required');
        $this->addValidationRule($request, 'email', 'email');
        $this->addValidationRule($request, 'email', 'unique', ['table' => 'users', 'column' => 'email']);
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

        // Store user
        $user = User::create(
            $request->input('email'),
            $request->input('password'),
            $request->input('name')
        );

        $user->signIn($request);

        return new HtmlResponse('registered.html');
    }

    /**
     * Log the user out
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse {

        $this->guard($request);
        $this->validateCsrf($request);

        $request->user()->signOut($request);

        return new RedirectResponse('/');
    }
}
