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

class UserController extends Controller {

    /**
     * The profile (me) page
     *
     * @param  Request $request
     * @return HtmlResponse
     */
    public function me(Request $request): HtmlResponse {
        $this->guard($request);
        return (new HtmlResponse('me.html', [
            'authenticated' => 'true',
            'name'          => $request->user()->getName(),
            'email'         => $request->user()->getEmail(),
        ]))->withCsrfToken($request);
    }

}
