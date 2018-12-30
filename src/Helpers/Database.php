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

}
