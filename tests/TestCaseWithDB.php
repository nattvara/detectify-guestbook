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

use Guestbook\Helpers\Database;
use PHPUnit\Framework\Constraint\Count;
use PHPUnit\Framework\TestCase;
use \PDO;
use \PDOException;

class TestCaseWithDB extends TestCase {

    /**
     * Database connection
     *
     * @var PDO
     */
    protected $db;

    /**
     * Setup test environment
     *
     * @return void
     */
    protected function setUp() {
        try {
            $this->db = Database::getPDOConnection(false);
        } catch (PDOException $e) {
            echo(PHP_EOL . 'Failed to connect to database. Skipping test.' . PHP_EOL);
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4;',
                getenv('host'),
                getenv('db')
            );
            echo(sprintf(
                'Tried to connect using the following dsn, username and password: "%s", "%s", "%s"',
                $dsn,
                getenv('username'),
                getenv('password')
            ));
            $this->markTestSkipped();
            return;
        }
        $this->dropDatabaseIfExists();
        $this->createDatabase();
        $this->createSchema();
    }

    /**
     * Teardown test environment
     *
     * @return void
     */
    protected function tearDown() {
        if ($this->db) {
            $this->dropDatabaseIfExists();
        }
    }

    /**
     * Drop test database if it exists
     *
     * @return void
     */
    protected function dropDatabaseIfExists() {
        $sql = sprintf('DROP DATABASE IF EXISTS `%s`;', getenv('db'));
        $this->db->exec($sql);
    }

    /**
     * Create database and reconnect
     *
     * @return void
     */
    protected function createDatabase() {
        $sql = sprintf('CREATE DATABASE IF NOT EXISTS `%s`;', getenv('db'));
        $this->db->exec($sql);
        $this->db = Database::getPDOConnection();
    }

    /**
     * Create schema
     *
     * @return void
     */
    protected function createSchema() {
        $sql = file_get_contents(__DIR__ . '/../database/schema.sql');
        $this->db->exec($sql);
    }

    /**
     * Assert database has a user with given public id
     *
     * @param  string $id public_id
     * @return void
     */
    protected function assertDatabaseHasUserWithPublicId(string $id) {

        $stmt = $this->db->prepare('SELECT * FROM `users` WHERE `public_id` = :public_id');
        $stmt->execute(['public_id' => $id]);
        $users = $stmt->fetchAll();

        $this->assertThat(
            $users,
            new Count(1),
            sprintf('Failed asserting user with public_id %s exists', $id)
        );

    }

    /**
     * Assert database has a message with given public id
     *
     * @param  string $id public_id
     * @return void
     */
    protected function assertDatabaseHasMessageWithPublicId(string $id) {

        $stmt = $this->db->prepare('SELECT * FROM `messages` WHERE `public_id` = :public_id');
        $stmt->execute(['public_id' => $id]);
        $messages = $stmt->fetchAll();

        $this->assertThat(
            $messages,
            new Count(1),
            sprintf('Failed asserting message with public_id %s exists', $id)
        );

    }

    /**
     * Assert database has a given number of messages
     *
     * @param  int   $count
     * @return void
     */
    protected function assertMessageCount(int $count) {

        $stmt = $this->db->prepare('SELECT * FROM `messages`;');
        $stmt->execute();
        $messages = $stmt->fetchAll();

        $this->assertThat(
            $messages,
            new Count($count),
            sprintf('Failed asserting message count was %d', $count)
        );

    }

}
