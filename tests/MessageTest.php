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

use Guestbook\Models\Message;
use Guestbook\Models\User;
use Tests\TestCaseWithDB;

class MessageTest extends TestCaseWithDB {

    /**
     * @var Message
     */
    private $message;

    /**
     * @var User
     */
    private $user;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();
        $this->message  = new Message;
        $this->user     = User::create('foo@email.com', 'secret', 'John Doe');
    }

    /**
     * Test message can be created
     *
     * @return void
     */
    public function test_message_can_be_created() {

        $this->message = Message::create('some interesting thought', $this->user);

        $this->assertInstanceOf(Message::class, $this->message);
        $this->assertDatabaseHasMessageWithPublicId($this->message->getPublicId());

    }

}
