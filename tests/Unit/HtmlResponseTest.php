<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit;

use Guestbook\Http\Responses\HtmlResponse;
use PHPUnit\Framework\TestCase;

class HtmlResponseTest extends TestCase {

    /**
     * @var HtmlResponse
     */
    private $response;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp() {
        $this->response = new HtmlResponse;
        $this->resourceDir = __DIR__ . '/../resources/';
    }

    /**
     * Test resource directory can be set
     *
     * @return void
     */
    public function test_html_response_resource_directory_can_be_set() {

        HtmlResponse::setResourceDirectory($this->resourceDir);
        $this->assertAttributeEquals($this->resourceDir, 'resourceDir', new HtmlResponse);

    }

    /**
     * Test html response can be created and output html string
     *
     * @return void
     */
    public function test_html_response_can_be_created_and_output_html() {

        HtmlResponse::setResourceDirectory($this->resourceDir);
        $this->response = new HtmlResponse('test.html');

        $html = file_get_contents($this->resourceDir . 'test.html');
        $this->assertEquals($html, $this->response->getResponseBody());

    }

}
