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

class PlainTextResponse extends Response {

    /**
     * Content type
     *
     * @var string
     */
    protected $contentType = 'text/plain';

    /**
     * New plaintext response
     *
     * @param string $responseBody
     */
    public function __construct(string $responseBody) {
        $this->setResponseBody($responseBody);
    }

}
