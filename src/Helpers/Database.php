<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Guestbook\Helpers;

use \PDO;

class Database {

    /**
     * Get PDO connection
     *
     * @param  bool $withDB if pdo should use database in connection dsn
     * @return PDO
     */
    public function getPdoConnection(bool $withDB = true): PDO {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4;',
            getenv('host'),
            $withDB ? getenv('db') : ''
        );
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];
        return new PDO($dsn, getenv('username'), getenv('password'), $options);
    }

    /**
     * Get table list in db
     *
     * @param  PDO    $db
     * @return array
     */
    public static function getTableList(PDO $db): array {

        $dbName = $db->query('SELECT DATABASE();')->fetchColumn();
        $stmt   = $db->prepare('SELECT `TABLE_NAME` FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_SCHEMA` = :db;');
        $stmt->execute(['db' => $dbName]);
        $rows   = $stmt->fetchAll();
        $tables = [];
        foreach ($rows as $row) {
            $tables[] = $row['TABLE_NAME'];
        }

        return $tables;

    }

    /**
     * Get column list in table
     *
     * @param  PDO    $db
     * @param  string $table
     * @return array
     */
    public static function getColumnList(PDO $db, string $table): array {

        $dbName = $db->query('SELECT DATABASE();')->fetchColumn();
        $stmt   = $db->prepare('SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA` = :db AND `TABLE_NAME` = :table;');
        $stmt->execute(['db' => $dbName, 'table' => $table]);
        $rows   = $stmt->fetchAll();
        $columns = [];
        foreach ($rows as $row) {
            $columns[] = $row['COLUMN_NAME'];
        }

        return $columns;

    }
}
