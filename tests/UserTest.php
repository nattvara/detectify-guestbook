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

use Guestbook\Models\Exceptions\InvalidPasswordException;
use Guestbook\Models\Exceptions\UserNotFoundException;
use Guestbook\Models\User;
use Tests\TestCaseWithDB;

class UserTest extends TestCaseWithDB {

    /**
     * @var User
     */
    private $user;

    /**
     * Test user can be created
     *
     * @return void
     */
    public function test_user_can_be_created() {

        $this->user = User::create('foo@email.com', 'secret', 'John Doe');
        $this->assertInstanceOf(User::class, $this->user);
        $this->assertDatabaseHasUserWithPublicId($this->user->getPublicId());

    }

    /**
     * Test user can be authenticated
     *
     * @return void
     */
    public function test_user_can_be_authenticated() {

        $this->user = User::create('foo@email.com', 'secret', 'John Doe');

        $login = User::authenticate('foo@email.com', 'secret');
        $this->assertEquals($login, $this->user);

    }

    /**
     * Test exception is thrown if password is invalid
     *
     * @return void
     */
    public function test_exception_is_thrown_if_password_is_invalid() {

        $this->user = User::create('foo@email.com', 'secret', 'John Doe');
        $this->expectException(InvalidPasswordException::class);

        User::authenticate('foo@email.com', 'wrong password');

    }

    /**
     * Test exception is thrown if email is invalid
     *
     * @return void
     */
    public function test_exception_is_thrown_if_user_is_not_found() {

        $this->user = User::create('foo@email.com', 'secret', 'John Doe');
        $this->expectException(UserNotFoundException::class);

        User::authenticate('invalid_email@email.com', 'secret');

    }

}
