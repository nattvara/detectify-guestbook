<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Guestbook\Http\Validation\Rules;

use Guestbook\Helpers\Database;
use Guestbook\Http\Validation\Exceptions\ValidationException;

class Unique extends Rule {

    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $column;

    /**
     * Execute rule on request input
     *
     * @param  array  $requestInput
     * @return void
     */
    public function execute(array $requestInput) {

        if ($this->recordExists($requestInput[$this->field])) {
            $this->fail(
                Rule::SEVERITY_DANGER,
                'We already have given {{field}} in our records. Please use another one.'
            );
            return;
        }

        $this->pass();
    }

    /**
     * Check if record exists with given value
     *
     * @param  mixed $value
     * @return bool
     * @throws ValidationException if table or column don't exist
     */
    private function recordExists($value): bool {

        $db     = Database::getPDOConnection();
        $tables = Database::getTableList($db);
        if (!in_array($this->table, $tables)) {
            throw new ValidationException(sprintf('Unkown table \'%s\'', $this->table));
        }

        $columns = Database::getColumnList($db, $this->table);
        if (!in_array($this->column, $columns)) {
            throw new ValidationException(sprintf('Unkown column \'%s\'', $this->column));
        }

        // Stripping everything but alphanumeric characters
        // from table and column, even though they have been
        // whitelisted by the checks against existing columns
        // and tables already - better to be on the safe side.
        $sql = sprintf(
            'SELECT * FROM `%s` WHERE `%s` = :value;',
            preg_replace('/[^a-z0-9.]+/i', '', $this->table),
            preg_replace('/[^a-z0-9.]+/i', '', $this->column)
        );

        $stmt = $db->prepare($sql);
        $stmt->execute(['value' => $value]);
        $row = $stmt->fetch();

        if (!$row) {
            return false;
        }

        return true;
    }

    /**
     * Read options
     *
     * @param  array  $options
     * @return void
     */
    public function readOptions(array $options) {

        if (!in_array('table', array_keys($options))) {
            throw new ValidationException('\'table\' must be defined in options for match rule');
        }

        if (!in_array('column', array_keys($options))) {
            throw new ValidationException('\'column\' must be defined in options for match rule');
        }

        $this->table    = $options['table'];
        $this->column   = $options['column'];
    }

}
