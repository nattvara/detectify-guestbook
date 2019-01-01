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
        $this->db = Database::getPDOConnection();
        $stmt = $this->db->prepare('
            INSERT INTO `cookies` (`user_id`, `token`, `created_at`)
            VALUES (:user_id, :token, NOW());
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

}
