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

class JsonResponse extends Response {

    /**
     * Content type
     *
     * @var string
     */
    protected $contentType = 'application/json';

    /**
     * New json response
     *
     * @param array $responseData
     */
    public function __construct(array $responseData) {
        $this->setResponseBody(json_encode($responseData));
    }

}
