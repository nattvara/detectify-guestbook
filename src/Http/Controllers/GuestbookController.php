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

class GuestbookController extends Controller {

    /**
     * The index page
     *
     * @param  Request $request
     * @return HtmlResponse
     */
    public function index(Request $request): HtmlResponse {
        return (new HtmlResponse('index.html', [
            'authenticated' => $request->hasAuthenticatedUser() ? 'true' : 'false',
            'name'          => $request->hasAuthenticatedUser() ? $request->user()->getName() : false,
            'email'         => $request->hasAuthenticatedUser() ? $request->user()->getEmail() : false,
        ]))->withCsrfToken($request);
    }
}
