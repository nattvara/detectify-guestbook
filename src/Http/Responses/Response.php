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

abstract class Response {

    /**
     * Response body
     *
     * @var string
     */
    private $responseBody = '';

    /**
     * Http status code
     *
     * @var int
     */
    private $statusCode;

    /**
     * Headers to include in response
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Set response body
     *
     * @param string $responseBody
     * @return void
     */
    public function setResponseBody(string $responseBody) {
        $this->responseBody = $responseBody;
    }

    /**
     * Get response body
     *
     * @return string
     */
    public function getResponseBody(): string {
        return $this->responseBody;
    }

    /**
     * Get content type
     *
     * @return string
     */
    public function getContentType(): string {
        return $this->contentType;
    }

    /**
     * Set status code of response
     *
     * @param  int    $code
     * @return self
     */
    public function withStatusCode(int $code): self {
        http_response_code($code);
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Get status code
     *
     * @return int
     */
    public function getStatusCode(): int {
        if (!$this->statusCode) {
            return 200;
        }
        return $this->statusCode;
    }

    /**
     * Write response
     *
     * @return void
     */
    public function write() {

        if ($this instanceof HtmlResponse) {
            $this->cleanupUnusedVariables();
        }

        foreach ($this->headers as $header) {
            header($header);
        }

        header(sprintf('Content-Type: %s', $this->contentType));
        $fp = fopen('php://output', 'w');
        fwrite($fp, $this->getResponseBody());
        fclose($fp);
    }

}
