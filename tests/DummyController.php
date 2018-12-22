<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests;

use Guestbook\Http\Controllers\Controller;
use Guestbook\Http\Request;
use Guestbook\Http\Responses\PlainTextResponse;

class DummyController extends Controller {

    /**
     * Copy of response that always will be returned
     *
     * @return PlainTextResponse
     */
    public static function getExpectedResponse(): PlainTextResponse {
        return new PlainTextResponse('foo');
    }

    /**
     * Return a simple plain text response
     *
     * @param  Request $request
     * @return PlainTextResponse
     */
    public function plain(Request $request): PlainTextResponse {
        return new PlainTextResponse('foo');
    }

}
