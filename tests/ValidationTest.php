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
use Guestbook\Http\Routes\POST;
use Guestbook\Http\Validation\RuleBuilder;
use Guestbook\Http\Validation\Validation;
use Guestbook\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Tests\TestCaseWithDB;

class ValidationTest extends TestCaseWithDB {

    /**
     * @var Validation
     */
    private $validation;

    /**
     * @var Request
     */
    private $request;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp() {

        parent::setUp();

        $this->validation = new Validation;

        $this->request = new Request;
        $this->request->setPath('/');
        $this->request->setMethod(POST::class);

    }

    /**
     * Rule can be added to validation operation
     *
     * @return void
     */
    public function test_rule_can_be_added_to_validation() {

        $rule = RuleBuilder::make('some_field', 'required');
        $this->validation->addRule($rule);

        $this->assertAttributeCount(1, 'rules', $this->validation);

    }

    /**
     * Validation can be perfomed on request
     *
     * @return void
     */
    public function test_validation_can_be_performed_on_request() {

        $this->request->setInput([
            'some_field' => 'some_value',
        ]);

        $rule = RuleBuilder::make('some_field', 'required');
        $this->validation->addRule($rule);
        $result = $this->validation->validate($this->request);

        $this->assertTrue($result);

    }


    /**
     * Errors can be retrieved if validation failed
     *
     * @return void
     */
    public function test_errors_can_be_retrieved_if_validation_failed() {

        $this->request->setInput([
            'some_field' => 'some_value',
        ]);

        $rule1 = RuleBuilder::make('some_field', 'required');
        $rule2 = RuleBuilder::make('some_other_field', 'required');
        $this->validation->addRule($rule1);
        $this->validation->addRule($rule2);
        $result = $this->validation->validate($this->request);

        $this->assertFalse($result);
        $this->assertEquals([
            [
                'field'             => 'some_other_field',
                'rule'              => 'required',
                'severity'          => 'danger',
                'human_friendly'    => '"Some_other_field" is required and is currently missing',
            ]
        ], $this->validation->getErrors());

    }

    /**
     * Test custom name for field name can be provided
     *
     * Some fields have underscores and computer friendly formats, such as
     * "some_other_field"
     * So it looks better to be able to override it with "Some other field"
     *
     * @return void
     */
    public function test_custom_name_can_be_provided_for_an_ugly_field_name() {

        $this->request->setInput([
            'some_field' => 'some_value',
        ]);

        $rule = RuleBuilder::make('some_field', 'required');
        $rule = RuleBuilder::make('some_other_field', 'required', [], 'Some other field');
        $this->validation->addRule($rule);
        $result = $this->validation->validate($this->request);

        $this->assertFalse($result);
        $this->assertEquals([
            [
                'field'             => 'some_other_field',
                'rule'              => 'required',
                'severity'          => 'danger',
                'human_friendly'    => '"Some other field" is required and is currently missing'
            ]
        ], $this->validation->getErrors());

    }

    /**
     * Test email rule
     *
     * @return void
     */
    public function test_email_rule() {

        $rule = RuleBuilder::make('email_field', 'email');
        $this->validation->addRule($rule);

        $this->request->setInput(['email_field' => 'foo@bar.com']);
        $this->assertTrue($this->validation->validate($this->request));

        $this->request->setInput(['email_field' => 'foobar.com']);
        $this->assertFalse($this->validation->validate($this->request));

        $this->request->setInput(['email_field' => 'foo@bar']);
        $this->assertFalse($this->validation->validate($this->request));

    }

    /**
     * Test length rule
     *
     * @return void
     */
    public function test_length_rule() {

        $rule = RuleBuilder::make('some_field', 'length', ['min' => 2, 'max' => 10]);
        $this->validation->addRule($rule);

        // To short
        $this->request->setInput(['some_field' => 'a']);
        $this->assertFalse($this->validation->validate($this->request));

        // To long
        $this->request->setInput(['some_field' => 'foobarbazba']); // 11 characters
        $this->assertFalse($this->validation->validate($this->request));

        // Working
        $this->request->setInput(['some_field' => 'perfect']);
        $this->assertTrue($this->validation->validate($this->request));

    }

    /**
     * Test match rule
     *
     * @return void
     */
    public function test_match_rule() {

        $rule = RuleBuilder::make('some_field', 'match', ['key' => 'some_other_field', 'key_pretty' => 'Some other field']);
        $this->validation->addRule($rule);

        // No match
        $this->request->setInput(['some_field' => 'foo', 'some_other_field' => 'bar']);
        $this->assertFalse($this->validation->validate($this->request));

        // Match
        $this->request->setInput(['some_field' => 'foo', 'some_other_field' => 'foo']);
        $this->assertTrue($this->validation->validate($this->request));

    }

    /**
     * Test pwnedpasswords.com rule
     *
     * @return void
     */
    public function test_pwnedpasswords_dot_com_rule() {

        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/resources/passwords_danger.txt')),
            new Response(200, [], file_get_contents(__DIR__ . '/resources/passwords_warning.txt')),
            new Response(200, [], file_get_contents(__DIR__ . '/resources/passwords_good.txt')),
        ]);
        $handler    = HandlerStack::create($mock);
        $client     = new Client(['handler' => $handler]);

        $rule = RuleBuilder::make('a_password', 'pwnedpasswords.com', ['mock' => $client]);
        $this->validation->addRule($rule);

        // Bad password
        $this->request->setInput(['a_password' => 'password1']);
        $this->assertFalse($this->validation->validate($this->request));
        $this->assertEquals([
            [
                'field'             => 'a_password',
                'rule'              => 'pwnedpasswords.com',
                'severity'          => 'danger',
                'human_friendly'    => '"A_password" was securely tested against real world data-leaks and your password apears to be really bad. Please consider another one. Read more at https://haveibeenpwned.com',
            ]
        ], $this->validation->getErrors());

        // A weak password
        $this->request->setInput(['a_password' => 'P@ssw0rd1234']);
        $this->assertFalse($this->validation->validate($this->request));
        $this->assertEquals([
            [
                'field'             => 'a_password',
                'rule'              => 'pwnedpasswords.com',
                'severity'          => 'warning',
                'human_friendly'    => '"A_password" was securely tested against real world data-leaks and your password apears to be weak. Please consider another one. Read more at https://haveibeenpwned.com',
            ]
        ], $this->validation->getErrors());

        // A good password
        $this->request->setInput(['a_password' => '353e8061f2befecb6818ba0c034c632fb0bcae1b']);
        $this->assertTrue($this->validation->validate($this->request));

    }

    /**
     * Test unique rule
     *
     * @return void
     */
    public function test_unique_rule() {

        // Create a few users
        User::create('foo@email.com', 'secret', 'John Doe');
        User::create('bar@email.com', 'secret', 'Jane Doe');

        $rule = RuleBuilder::make('some_field', 'unique', ['table' => 'users', 'column' => 'email']);
        $this->validation->addRule($rule);

        // Not unique
        $this->request->setInput(['some_field' => 'foo@email.com']);
        $this->assertFalse($this->validation->validate($this->request));

        // Is unique
        $this->request->setInput(['some_field' => 'baz@email.com']);
        $this->assertTrue($this->validation->validate($this->request));

    }

}
