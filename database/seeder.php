<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Guestbook\Helpers\Database;
use Guestbook\Models\Exceptions\ReplyDepthException;
use Guestbook\Models\Exceptions\VotingException;
use Guestbook\Models\Message;
use Guestbook\Models\User;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../.env.php');

const RANDOM_SEED = 42;

function dropDatabaseIfExists(PDO $db) {
    $sql = sprintf('DROP DATABASE IF EXISTS `%s`;', getenv('db'));
    $db->exec($sql);
}
function createDatabase(PDO $db) {
    $sql = sprintf('CREATE DATABASE IF NOT EXISTS `%s`;', getenv('db'));
    $db->exec($sql);
    $db = Database::getPDOConnection();
}
function createSchema(PDO $db) {
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $db->exec($sql);
}

srand(RANDOM_SEED);
date_default_timezone_set(getenv('timezone'));

echo('creating db' . PHP_EOL);
$db = Database::getPDOConnection(false);
dropDatabaseIfExists($db);
createDatabase($db);

$db = Database::getPDOConnection();
createSchema($db);

echo('creating users' . PHP_EOL);

$users = [
    'batman'    => User::create('thebat@secret-cave.com', 'fearme', 'Batman'),
    'bruce'     => User::create('bruce.wayne@wayne-corp.com', 'rachel<3', 'Bruce Wayne'),
    'joker'     => User::create('hAHahahAHahahahaa@why-so-serious.poker', bin2hex(random_bytes(1000)), 'The Joker'),
    'alfred'    => User::create('alfred1935@aol.com', 'funnel fairy butter bar', 'Alfred Pennyworth'),
    'harvey'    => User::create('hdent@gotham.gov', 'i make my own luck', 'Harvey Dent'),
    'rachel'    => User::create('rdawes@gotham.gov', 'ProsecutorGirl88', 'Rachel Dawes'),
    'gordon'    => User::create('jgordon@gotham.gov', 'roftopsquad', 'James Gordon'),
    'fox'       => User::create('fox@wayne-corp.com', 'horse.battery.staple', 'Lucius Fox'),
];

echo('reading strings' . PHP_EOL);
$messages = [];
foreach (array_keys($users) as $user) {
    $strings = file_get_contents(sprintf('%s/strings/%s.json', __DIR__, $user));
    $strings = json_decode($strings);
    foreach ($strings as $msg) {
        $messages[] = [
            'user' => $user,
            'message' => $msg
        ];
    }
}

echo('generating threads' . PHP_EOL);
for ($i = 0; $i < 30; $i++) {
    $message = $messages[rand(0, count($messages) - 1)];
    $reply   = rand(0, 4);
    if ($reply < 1 || $i == 0) {
        Message::create($message['message'], $users[$message['user']]);
    } else {
        while (true) {
            try {
                $all    = Message::all();
                $parent = $all[rand(0, count($all) - 1)];
                Message::create($message['message'], $users[$message['user']], $parent);
                break;
            } catch (ReplyDepthException $e) {}
        }
    }
}

echo('voting on messages' . PHP_EOL);
$messages = Message::all();
foreach ($users as $user) {
    $votes = rand(5, count($messages) - 1);
    while ($votes > 0) {
        try {
            $message = $messages[rand(0, count($messages) - 1)];
            if (rand(0, 1)) {
                $message->upvote($user);
            } else {
                $message->downvote($user);
            }
            $votes--;
        } catch (VotingException $e) {}
    }
}
