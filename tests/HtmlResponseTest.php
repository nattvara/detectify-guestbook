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

use Guestbook\Http\Request;
use Guestbook\Http\Responses\HtmlResponse;
use Guestbook\Http\Routes\GET;
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
        $this->resourceDir = __DIR__ . '/resources/';
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

    /**
     * Test variables can be injected to template
     *
     * @return void
     */
    public function test_variables_can_be_injected_into_template() {

        HtmlResponse::setResourceDirectory($this->resourceDir);
        $this->response = new HtmlResponse('variables.html', [
            'var1' => 'foo',
            'var2' => 'bar',
        ]);

        $this->assertStringContainsString('foo', $this->response->getResponseBody());
        $this->assertStringContainsString('bar', $this->response->getResponseBody());

    }

    /**
     * Test csrf token can be generated and stored in session
     *
     * @return void
     */
    public function test_csrf_can_be_generated_and_stored_in_session() {

        $request = new Request;
        $request->setPath('/');
        $request->setMethod(GET::class);

        HtmlResponse::setResourceDirectory($this->resourceDir);
        $this->response = (new HtmlResponse('csrf.html'))->withCsrfToken($request);

        $csrfInput = sprintf(
            '<input type="hidden" name="csrf_token" value="%s">',
            $request->getCsrfToken()
        );
        $this->assertStringContainsString($csrfInput, $this->response->getResponseBody());

    }

}
