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

use Guestbook\Models\Exceptions\ReplyDepthException;
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
        $this->user     = User::create('foo@email.com', 'secret', 'John Doe');
        $this->message  = Message::create('some interesting thought', $this->user);
    }

    /**
     * Test message can be created
     *
     * @return void
     */
    public function test_message_can_be_created() {

        $this->assertInstanceOf(Message::class, $this->message);
        $this->assertDatabaseHasMessageWithPublicId($this->message->getPublicId());

    }

    /**
     * Message can be retrieved by public_id and id
     *
     * @return void
     */
    public function test_message_can_be_retrieved_by_ids() {

        $msg = Message::findByPublicId($this->message->getPublicId());
        $msg->loadDependencies();
        $this->assertEquals($this->message, $msg);

        $msg = Message::findById($this->message->getId());
        $msg->loadDependencies();
        $this->assertEquals($this->message, $msg);

    }

    /**
     * Test message can be responded to
     *
     * @return void
     */
    public function test_message_can_be_responded_to() {

        $response = Message::create('a thoughtful response', $this->user, $this->message);
        $this->assertEquals($this->message, $response->getParentMessage());

        $response = Message::findById($response->getId());
        $this->assertEquals($this->message, $response->getParentMessage()->loadDependencies());

    }

    /**
     * Test reply depth exception is thrown if amount of replies is to large
     *
     * @return void
     */
    public function test_reply_depth_limit_can_be_reached() {

        $limit = (int) getenv('message_reply_limit');
        for ($i = 1; $i <= $limit + 1; $i++) {
            if ($i + 1 == $limit) {
                $this->expectException(ReplyDepthException::class);
            }
            $this->message = Message::create(
                'a thoughtful response',
                $this->user,
                $this->message
            );
            $this->assertMessageCount($i + 1); // Replies + root message
        }

    }
}
