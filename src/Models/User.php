<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Guestbook\Models;

use Guestbook\Helpers\Database;
use Guestbook\Models\Exceptions\InvalidPasswordException;
use Guestbook\Models\Exceptions\UserNotFoundException;
use \PDO;

class User {

    /**
     * @var string
     */
    private $publicId;

    /**
     * @var string
     */
    private $email;

    /**
     * Password hash
     *
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $name;

    /**
     * @var PDO
     */
    private $db;

    /**
     * Create new user
     *
     * @param  string $email
     * @param  string $password clear text
     * @param  string $name
     * @return User
     */
    public static function create(string $email, string $password, string $name) {
        $user = new User;
        $user->setPublicId(User::generatePublicId());
        $user->setEmail($email);
        $user->setPassword(User::makePasswordHash($password));
        $user->setName($name);
        $user->store();
        return $user;
    }

    /**
     * Authenticate and retrieve a user
     *
     * @param  string $email
     * @param  string $cleartextPassword
     * @return User
     * @throws InvalidPasswordException
     */
    public static function authenticate(string $email, string $cleartextPassword): User {
        $user = User::findByEmail($email);
        if (!$user->checkPassword($cleartextPassword)) {
            throw new InvalidPasswordException('password did not match hashed password');
        }
        return $user;
    }

    /**
     * Find user by email
     *
     * @param  string $email
     * @return User
     * @throws UserNotFoundException
     */
    public static function findByEmail(string $email): User {
        $db = Database::getPDOConnection();
        $stmt = $db->prepare('SELECT * FROM `users` WHERE `email` = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new UserNotFoundException(sprintf('no user with email \'%s\' exists', $email));
        }
        $user = new User;
        $user->setDB($db);
        $user->setName($row['name']);
        $user->setEmail($row['email']);
        $user->setPublicId($row['public_id']);
        $user->setPassword($row['password']);
        return $user;
    }

    /**
     * Set public id
     *
     * @param string $publicId
     * @return void
     */
    public function setPublicId(string $publicId) {
        $this->publicId = $publicId;
    }

    /**
     * Get public id
     *
     * @return string
     */
    public function getPublicId(): string {
        return $this->publicId;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return void
     */
    public function setEmail(string $email) {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail(): string {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password hashed password
     * @return void
     */
    public function setPassword(string $password) {
        $this->password = $password;
    }

    /**
     * Check if password matches hashed password in db
     *
     * @param  string $cleartext
     * @return bool
     */
    public function checkPassword(string $cleartext): bool {
        return password_verify($cleartext, $this->password);
    }

    /**
     * Set db instance
     *
     * @param PDO $db
     * @return void
     */
    public function setDB(PDO $db) {
        $this->db = $db;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name) {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Store user in database
     *
     * @return void
     */
    private function store() {
        if (!$this->db) {
            $this->db = Database::getPDOConnection();
        }
        $stmt = $this->db->prepare('
            INSERT INTO `users` (`public_id`, `email`, `password`, `name`)
            VALUES (:public_id, :email, :password, :name);
        ');
        $stmt->execute([
            'public_id' => $this->publicId,
            'email'     => $this->email,
            'password'  => $this->password,
            'name'      => $this->name,
        ]);
    }

    /**
     * Make password hash
     *
     * @param  string $cleartext
     * @return string            hashed password
     */
    public static function makePasswordHash(string $cleartext): string {
        return password_hash($cleartext, PASSWORD_DEFAULT, ['cost' => 12]);
    }

    /**
     * Generate user id
     *
     * @return string
     */
    private static function generatePublicId(): string {
        $hash   = substr(hash('sha256', bin2hex(random_bytes(10000))), 0, 27);
        $id     = sprintf('user-%s', $hash);
        return $id;
    }

    /**
     * Convert object to array
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'public_id' => $this->public_id,
            'email'     => $this->email,
            'name'      => $this->name,
        ];
    }

}
