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

use Guestbook\Http\Responses\Traits\ReadsTemplates;

class HtmlResponse extends Response {

    use ReadsTemplates;

    /**
     * Content type
     *
     * @var string
     */
    protected $contentType = 'text/html';

}
