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
use \PDO;

class Cookie {

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $token;

    /**
     * New cookie
     *
     * @param User $user
     */
    public function __construct(User $user) {
        $this->user     = $user;
        $this->token    = Cookie::generateToken();
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken(): string {
        return $this->token;
    }

    /**
     * Store cookie in database
     *
     * @return void
     */
    public function store() {
        $db = Database::getPDOConnection();
        $stmt = $db->prepare('
            INSERT INTO `cookies` (`user_id`, `token`, `valid`, `created_at`, `updated_at`)
            VALUES (:user_id, :token, 1, NOW(), NOW());
        ');
        $stmt->execute([
            'user_id'   => $this->user->getId(),
            'token'     => $this->token,
        ]);
    }

    /**
     * Generate cookie token
     *
     * @return string
     */
    private static function generateToken(): string {
        return sprintf('token-%s', bin2hex(random_bytes(125)));
    }

    /**
     * Deactivate token, so it will no longer be valid for logging in
     *
     * @param  string $token
     * @return void
     */
    public function deactivateToken(string $token) {

        $db = Database::getPDOConnection();
        $stmt = $db->prepare('
            UPDATE `cookies` SET `valid` = 0, `updated_at` = NOW()
            WHERE `token` = :token
        ');
        $stmt->execute(['token' => $token]);

    }

}
