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
use Guestbook\Models\Exceptions\VotingException;
use \DateTime;
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
     * @var DateTime
     */
    private $createdAt;

    /**
     * @var int
     */
    private $upvotes;

    /**
     * @var int
     */
    private $downvotes;

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
     * Fetch all messages
     *
     * @return array
     */
    public static function all(): array {
        $db         = Database::getPDOConnection();
        $stmt       = $db->prepare('SELECT * FROM `messages`;');
        $stmt->execute();
        $rows       = $stmt->fetchAll();
        $messages   = [];
        foreach ($rows as $row) {
            $messages[] = Message::fromDBRecord($row, $db);
        }
        return $messages;
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
        $message->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', $record['created_at']));
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
     * Set created at
     *
     * @param DateTime $createdAt
     * @return void
     */
    public function setCreatedAt(DateTime $createdAt) {
        $this->createdAt = $createdAt;
    }

    /**
     * Get created at
     *
     * @return DateTime
     */
    public function getCreatedAt(): DateTime {
        return $this->createdAt;
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
        $stmt = $this->db->prepare('SELECT `id`, `created_at` FROM `messages` WHERE `public_id` = :id;');
        $stmt->execute(['id' => $this->publicId]);
        $row = $stmt->fetch();
        $this->setId($row['id']);
        $this->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', $row['created_at']));
    }

    /**
     * Upvote message
     *
     * @param  User   $voter
     * @return void
     */
    public function upvote(User $voter) {
        $this->removeVoteIfCast($voter);
        $this->castVote($voter, 1);
    }

    /**
     * Downvote message
     *
     * @param  User   $voter
     * @return void
     */
    public function downvote(User $voter) {
        $this->removeVoteIfCast($voter);
        $this->castVote($voter, 0);
    }

    /**
     * Cast a vote on the message
     *
     * @param  User   $voter
     * @param  int    $sentiment 1 for upvote 0 for downvote
     * @return void
     */
    private function castVote(User $voter, int $sentiment) {
        if ($this->getAuthor()->getId() === $voter->getId()) {
            throw new VotingException('user cannot vote on their own messages');
        }
        $stmt = $this->db->prepare('
            INSERT INTO `votes` (`message_id`, `cast_by`, `sentiment`, `cast_at`)
            VALUES (:message_id, :cast_by, :sentiment, NOW());
        ');
        $stmt->execute([
            'message_id'    => $this->id,
            'cast_by'       => $voter->getId(),
            'sentiment'     => $sentiment
        ]);
        if ($sentiment) {
            $this->upvotes++;
        } else {
            $this->downvotes++;
        }
    }

    /**
     * Remove vote cast on message if cast by user
     *
     * @param  User   $voter
     * @return void
     */
    private function removeVoteIfCast(User $voter) {
        $stmt = $this->db->prepare('
            DELETE FROM `votes`
            WHERE `message_id` = :message_id
            AND `cast_by` = :cast_by;
        ');
        $stmt->execute([
            'message_id'    => $this->id,
            'cast_by'       => $voter->getId(),
        ]);
        $this->fetchVotes();
    }

    /**
     * Get upvotes
     *
     * @return int
     */
    public function getUpvotes(): int {
        if (!$this->upvotes) {
            $this->fetchVotes();
        }
        return $this->upvotes;
    }

    /**
     * Get downvotes
     *
     * @return int
     */
    public function getDownvotes(): int {
        if (!$this->downvotes) {
            $this->fetchVotes();
        }
        return $this->downvotes;
    }

    /**
     * Fetch vote count
     *
     * @return void
     */
    private function fetchVotes() {

        $sql = '
            SELECT COUNT(*) AS `votes`
            FROM `votes`
            WHERE `message_id` = :id
            AND `sentiment` = :sentiment;
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $this->id, 'sentiment' => 1]);
        $this->upvotes = (int) $stmt->fetch()['votes'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $this->id, 'sentiment' => 0]);
        $this->downvotes = (int) $stmt->fetch()['votes'];
    }

    /**
     * Get value of vote by user if cast
     *
     * Returns null if user haven't cast a vote
     *
     * @param  User   $voter
     * @return string|null
     */
    public function getVote(User $voter): ?string {
        $stmt = $this->db->prepare('
            SELECT `sentiment` FROM `votes`
            WHERE `message_id` = :message_id
            AND `cast_by` = :cast_by;
        ');
        $stmt->execute([
            'message_id'    => $this->id,
            'cast_by'       => $voter->getId(),
        ]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        if ((int) $row['sentiment']) {
            return 'up';
        }
        return 'down';
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
     * Format message data for frontend clients to consume
     *
     * @param  User $user optional, used for checking if user have cast a vote on message
     * @return array
     */
    public function formatForClient(?User $user = null): array {
        return [
            'id'            => $this->getPublicId(),
            'author'        => $this->getAuthor()->getName(),
            'author_id'     => $this->getAuthor()->getPublicId(),
            'parent_id'     => $this->hasParentMessage() ? $this->getParentMessage()->getPublicId() : null,
            'text'          => $this->getText(),
            'created_at'    => $this->getCreatedAt()->format(DateTime::ISO8601),
            'votes'         => [
                'my_vote'   => $user ? $this->getVote($user) : null,
                'up'        => $this->getUpvotes(),
                'down'      => $this->getDownvotes(),
            ]
        ];
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
