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

use Guestbook\Http\Controllers\Exceptions\InvalidCsrfException;
use Guestbook\Http\Request;

abstract class Controller {

    /**
     * Guard a route with check for valid CSRF token
     *
     * @param  Request $request
     * @return void
     * @throws InvalidCsrfException
     */
    protected function validateCsrf(Request $request) {
        if (!$request->containsValidCsrfToken()) {
            throw new InvalidCsrfException('Invalid CSRF token');
        }
    }

}
