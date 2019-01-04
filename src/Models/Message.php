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

class Message {

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $publicId;

    /**
     * @var string
     */
    private $text;

    /**
     * @var User
     */
    private $author;

    /**
     * @var PDO
     */
    private $db;

    /**
     * Create message and store in db
     *
     * @param  string $text
     * @param  User   $author
     * @return Message
     */
    public static function create(string $text, User $author): Message {
        $message = new Message;
        $message->setText($text);
        $message->setAuthor($author);
        $message->setPublicId(Message::generatePublicId());
        $message->store();
        return $message;
    }

    /**
     * Set message text
     *
     * @param string $text
     * @return void
     */
    public function setText(string $text) {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText(): string {
        return $this->text;
    }

    /**
     * Set author
     *
     * @param User $author
     * @return void
     */
    public function setAuthor(User $author) {
        $this->author = $author;
    }

    /**
     * Get author
     *
     * @return User
     */
    public function getAuthor(): User {
        return $this->author;
    }

    /**
     * Set id
     *
     * @param int $id
     * @return void
     */
    public function setId(int $id) {
        $this->id = $id;
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
     * Store user
     *
     * @return void
     */
    private function store() {
        if (!$this->db) {
            $this->db = Database::getPDOConnection();
        }
        $stmt = $this->db->prepare('
            INSERT INTO `messages` (`public_id`, `text`, `author_id`, `created_at`)
            VALUES (:public_id, :text, :author_id, NOW());
        ');
        $stmt->execute([
            'public_id' => $this->publicId,
            'text'      => $this->text,
            'author_id' => $this->author->getId(),
        ]);

        // Fetch the id for the message just created
        $stmt = $this->db->prepare('SELECT `id` FROM `messages` WHERE `public_id` = :id;');
        $stmt->execute(['id' => $this->publicId]);
        $row = $stmt->fetch();
        $this->setId($row['id']);
    }

    /**
     * Generate message id
     *
     * @return string
     */
    private static function generatePublicId(): string {
        $hash   = substr(hash('sha256', bin2hex(random_bytes(10000))), 0, 28);
        $id     = sprintf('msg-%s', $hash);
        return $id;
    }

}
