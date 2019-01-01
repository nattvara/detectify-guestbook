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

class WelcomeController extends Controller {

    /**
     * The index page
     *
     * @param  Request $request
     * @return HtmlResponse
     */
    public function index(Request $request): HtmlResponse {
        $this->guest($request);
        return new HtmlResponse('index.html');
    }

    /**
     * The profile (me) page
     *
     * @param  Request $request
     * @return HtmlResponse
     */
    public function me(Request $request): HtmlResponse {
        $this->guard($request);
        return (new HtmlResponse('me.html', [
            'name' => $request->user()->getName()
        ]))->withCsrfToken($request);
    }

}
