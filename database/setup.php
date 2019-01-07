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

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../.env.php');

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

echo('creating db' . PHP_EOL);
$db = Database::getPDOConnection(false);
dropDatabaseIfExists($db);
createDatabase($db);

$db = Database::getPDOConnection();
createSchema($db);
