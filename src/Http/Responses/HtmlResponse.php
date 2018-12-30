<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Guestbook\Http\Responses;

use Guestbook\Http\Request;
use Guestbook\Http\Responses\Traits\ReadsTemplates;

class HtmlResponse extends Response {

    use ReadsTemplates;

    /**
     * Content type
     *
     * @var string
     */
    protected $contentType = 'text/html';

    /**
     * Return html response with csrf tokens in any form
     *
     * Repalces {{csrf}} with hidden input tag
     *
     * @param  Request $request
     * @return HtmlResponse
     */
    public function withCsrfToken(Request $request): HtmlResponse {
        $request->generateCsrfToken();
        $body = $this->getResponseBody();
        $body = str_replace(
            '{{csrf}}',
            sprintf('<input type="hidden" name="csrf_token" value="%s">', $request->getCsrfToken()),
            $body
        );
        $this->setResponseBody($body);
        return $this;
    }
}
