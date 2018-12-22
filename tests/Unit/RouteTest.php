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

use Guestbook\Http\Routes\GET;
use PHPUnit\Framework\TestCase;
use Tests\DummyController;

class RouteTest extends TestCase {

    /**
     * @var Route
     */
    private $route;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp() {
        $this->route = new GET('/foo', DummyController::class, 'plain');
    }

    /**
     * Test path can be retrieved
     *
     * @return void
     */
    public function test_path_can_be_retrieved() {

        $this->assertSame('/foo', $this->route->getPath());

    }

}
