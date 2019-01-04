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
use Guestbook\Models\Exceptions\MessageNotFoundException;
use Guestbook\Models\Exceptions\ReplyDepthException;
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
     * @var int
     */
    private $authorId;

    /**
     * @var Message
     */
    private $parentMessage;

    /**
     * @var int
     */
    private $parentMessageId;

    /**
     * @var PDO
     */
    private $db;

    /**
     * Create message and store in db
     *
     * @param  string  $text
     * @param  User    $author
     * @param  Message $parent optional, if message is a reply to another message
     * @throws ReplyDepthException
     * @return Message
     */
    public static function create(string $text, User $author, ?Message $parent = null): Message {
        $message = new Message;
        $message->setText($text);
        $message->setAuthor($author);
        $message->setPublicId(Message::generatePublicId());
        if ($parent) {
            $depth = Message::getDepthOfMessage($parent) + 1;
            $message->setParentMessage($parent);
            $limit = (int) getenv('message_reply_limit');
            if ($depth > $limit) {
                throw new ReplyDepthException(sprintf('message was posted as a reply at a greater depth "%d" than allowed "%d"', $depth, $limit));
            }
        }
        $message->store();
        return $message;
    }

    /**
     * Find message by public id
     *
     * @param  string $id public_id
     * @return Message
     * @throws MessageNotFoundException
     */
    public static function findByPublicId(string $id): Message {
        $db = Database::getPDOConnection();
        $stmt = $db->prepare('SELECT * FROM `messages` WHERE `public_id` = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new MessageNotFoundException(sprintf('no message with id \'%s\' exists', $id));
        }
        $message = Message::fromDBRecord($row, $db);
        return $message;
    }

    /**
     * Find message by id
     *
     * @param  int     $id
     * @return Message
     * @throws MessageNotFoundException
     */
    public static function findById(int $id): Message {
        $db = Database::getPDOConnection();
        $stmt = $db->prepare('SELECT * FROM `messages` WHERE `id` = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new MessageNotFoundException(sprintf('no message with id \'%d\' exists', $id));
        }
        $message = Message::fromDBRecord($row, $db);
        return $message;
    }

    /**
     * Create message from database record
     *
     * @param  array  $record
     * @param  PDO    $db
     * @return Message
     */
    private static function fromDBRecord(array $record, $db): Message {
        $message = new Message;
        $message->setId($record['id']);
        $message->setText($record['text']);
        $message->setAuthorId($record['author_id']);
        $message->setPublicId($record['public_id']);
        $message->setDB($db);
        if ($record['parent_id']) {
            $message->setParentMessageId($record['parent_id']);
        }
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
        $this->author   = $author;
        $this->authorId = $author->getId();
    }

    /**
     * Set author id
     *
     * @param int $authorId
     * @return void
     */
    public function setAuthorId(int $authorId) {
        $this->authorId = $authorId;
    }

    /**
     * Get author
     *
     * @return User
     */
    public function getAuthor(): User {
        if ($this->author) {
            return $this->author;
        }
        $this->author = User::findById($this->authorId);
        return $this->getAuthor();
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
     * Get id
     *
     * @return int
     */
    public function getId(): int {
        return $this->id;
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
     * Set parent message
     *
     * @param Message $parentMessage
     * @return void
     */
    public function setParentMessage(Message $parentMessage) {
        $this->parentMessage    = $parentMessage;
        $this->parentMessageId  = $parentMessage->getId();
    }

    /**
     * Set parent message id
     *
     * @param int $parentMessageId
     * @return void
     */
    public function setParentMessageId(int $parentMessageId) {
        $this->parentMessageId = $parentMessageId;
    }

    /**
     * Get parent message
     *
     * @return Message|null
     */
    public function getParentMessage(): ?Message {
        if ($this->parentMessage) {
            return $this->parentMessage;
        }
        if (!$this->parentMessageId) {
            return null;
        }
        $this->parentMessage = Message::findById($this->parentMessageId);
        return $this->getParentMessage();
    }

    /**
     * Check if message has a parent message
     *
     * @return boolean
     */
    public function hasParentMessage(): bool {
        return isset($this->parentMessageId);
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
     * Store user
     *
     * @return void
     */
    private function store() {
        if (!$this->db) {
            $this->db = Database::getPDOConnection();
        }
        $stmt = $this->db->prepare('
            INSERT INTO `messages` (`public_id`, `text`, `author_id`, `parent_id`, `created_at`)
            VALUES (:public_id, :text, :author_id, :parent_id, NOW());
        ');
        $stmt->execute([
            'public_id' => $this->publicId,
            'text'      => $this->text,
            'author_id' => $this->author->getId(),
            'parent_id' => $this->parentMessageId,
        ]);

        // Fetch the id for the message just created
        $stmt = $this->db->prepare('SELECT `id` FROM `messages` WHERE `public_id` = :id;');
        $stmt->execute(['id' => $this->publicId]);
        $row = $stmt->fetch();
        $this->setId($row['id']);
    }

    /**
     * Load dependencies
     *
     * Call this method to make sure all dependent models have been loaded,
     * useful for testing
     *
     * @return self
     */
    public function loadDependencies() {
        $this->getAuthor();
        $this->getParentMessage();
        return $this;
    }

    /**
     * Get depth of message in tree from a root message (0 is root message)
     *
     * @param  Message $message
     * @return int
     */
    private static function getDepthOfMessage(Message $message): int {
        $depth = 0;
        while ($message->hasParentMessage()) {
            $depth++;
            $message = $message->getParentMessage();
        }
        return $depth;
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
