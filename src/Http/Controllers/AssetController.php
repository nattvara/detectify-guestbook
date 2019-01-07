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
use Guestbook\Http\Responses\CssResponse;
use Guestbook\Http\Responses\ImageResponse;
use Guestbook\Http\Responses\JavascriptResponse;

class AssetController extends Controller {

    /**
     * app.js
     *
     * @param  Request $request
     * @return JavascriptResponse
     */
    public function js(Request $request): JavascriptResponse {
        return new JavascriptResponse('main.js');
    }

    /**
     * app.css
     *
     * @param  Request $request
     * @return CssResponse
     */
    public function css(Request $request): CssResponse {
        return new CssResponse('app.css');
    }

    /**
     * logo.png
     *
     * @param  Request $request
     * @return ImageResponse
     */
    public function logo(Request $request): ImageResponse {
        return new ImageResponse('logo.png');
    }

}
