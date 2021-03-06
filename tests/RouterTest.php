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

use Guestbook\Http\Exceptions\RouteNotFoundException;
use Guestbook\Http\Request;
use Guestbook\Http\Responses\HtmlResponse;
use Guestbook\Http\Router;
use Guestbook\Http\Routes\GET;
use Guestbook\Http\Routes\POST;
use PHPUnit\Framework\TestCase;
use Tests\DummyController;

class RouterTest extends TestCase {

    /**
     * @var Router
     */
    private $router;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp() {
        $this->router = new Router;
    }

    /**
     * Test route can be added
     *
     * @return void
     */
    public function test_route_can_be_added() {

        $this->router->registerRoute(new GET('/', DummyController::class, 'plain'));
        $this->assertAttributeCount(1, 'routes', $this->router);

    }

    /**
     * Test multiple routes can be added
     *
     * @return void
     */
    public function test_multiple_routes_can_be_added() {

        $this->router->registerRoutes([
            new GET('/', DummyController::class, 'plain'),
            new GET('/foo/bar', DummyController::class, 'plain')
        ]);

        // Should not overwrite the ones that exist
        $this->router->registerRoutes([
            new GET('/bar', DummyController::class, 'plain'),
            new GET('/bar/baz', DummyController::class, 'plain')
        ]);

        $this->assertAttributeCount(4, 'routes', $this->router);

    }

    /**
     * Test route can be retrieved
     *
     * @return void
     */
    public function test_route_can_be_retrieved() {

        $route = new GET('/foo', DummyController::class, 'plain');
        $this->router->registerRoute($route);

        $this->assertSame($route, $this->router->retrieveRoute(GET::class, '/foo'));

    }

    /**
     * Test exception is thrown if route was not found
     *
     * @return void
     */
    public function test_exception_is_thrown_if_route_was_not_found() {

        $this->expectException(RouteNotFoundException::class);
        $this->router->retrieveRoute(GET::class, '/foo');

    }

    /**
     * Test router executes method for route and returns response
     *
     * @return void
     */
    public function test_router_executes_controller_on_route() {

        $expectedResponse = DummyController::getExpectedResponse();
        $request = new Request;
        $request->setPath('/');
        $request->setMethod(GET::class);

        $this->router->registerRoute(new GET('/', DummyController::class, 'plain'));

        $this->assertEquals($expectedResponse, $this->router->route($request));

    }

    /**
     * 404 page is returned if route isn't found
     *
     * @return void
     */
    public function test_404_page_is_returned_if_route_does_not_exist() {

        HtmlResponse::setResourceDirectory(__DIR__ . '/resources/');
        $expectedResponse = (new HtmlResponse('404.html'))->withStatusCode(404);

        $request = new Request;
        $request->setPath('/nowhere');
        $request->setMethod(GET::class);

        $this->assertEquals($expectedResponse, $this->router->route($request));

    }

    /**
     * Test variables in post request can be parsed and used
     *
     * @return void
     */
    public function test_variables_in_post_request_can_be_used_inside_controllers() {

        $request = new Request;
        $request->setPath('/');
        $request->setMethod(POST::class);
        $request->setInput([
            'number1' => 2,
            'number2' => 3,
        ]);

        $this->router->registerRoute(new POST('/', DummyController::class, 'addNumbers'));

        $response = $this->router->route($request);
        $this->assertStringContainsString(5, $response->getResponseBody());

    }

    /**
     * Add support for url variables
     *
     * @return void
     */
    public function test_url_variables_can_be_used_inside_controllers() {

        $request = new Request;
        $request->setPath('/some/path/foo/bar/baz'); // foo and bar are variables
        $request->setMethod(GET::class);

        $this->router->registerRoute(
            new GET('/some/path/$var_1/$var_2/baz', DummyController::class, 'printVarOneAndTwo')
        );

        $response = $this->router->route($request);
        $this->assertStringContainsString('foo', $response->getResponseBody());
        $this->assertStringContainsString('bar', $response->getResponseBody());

    }

}
