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

use Guestbook\Http\Validation\Exceptions\ValidationException;

abstract class Rule {

    /**
     * @var const error level danger
     */
    const SEVERITY_DANGER = 'danger';

    /**
     * @var const error level warning
     */
    const SEVERITY_WARNING = 'warning';

    /**
     * Rule type as string
     *
     * @var string
     */
    private $ruleType;

    /**
     * Name of input field to perform validation on
     *
     * @var string
     */
    protected $field;

    /**
     * Name to use in error message for humans if field name is not pretty
     *
     * @var string
     */
    private $fieldNamePretty;

    /**
     * If rule passed
     *
     * @var boolean
     */
    private $passed = false;

    /**
     * Error message if rule did not pass
     *
     * @var string
     */
    private $errorMessage = '';

    /**
     * Error severity
     *
     * @var string
     */
    private $errorSeverity;

    /**
     * New rule
     *
     * @param string $type
     */
    public function __construct(string $type) {
        $this->ruleType = $type;
    }

    /**
     * Set field to execute rule on
     *
     * @param string $field
     * @return void
     */
    public function setField(string $field) {
        $this->field = $field;
    }

    /**
     * Set pretty field name
     *
     * @param string $fieldNamePretty
     * @return void
     */
    public function setPrettyFieldName(string $fieldNamePretty) {
        $this->fieldNamePretty = $fieldNamePretty;
    }

    /**
     * Default method that reads options, can be overriden by rule if neccessary
     *
     * @param  array  $options
     * @return void
     * @throws ValidationException
     */
    public function readOptions(array $options) {
        if (empty($options)) {
            return;
        }
        throw new ValidationException(sprintf(
            'Rule \'%s\' did not expect any options',
            get_class($this)
        ));
    }

    /**
     * Check if rule passed
     *
     * @return bool
     */
    public function passed(): bool {
        return $this->passed;
    }

    /**
     * Rule passed
     *
     * @return void
     */
    public function pass() {
        $this->passed = true;
    }

    /**
     * Rule failed
     *
     * @param  string $severity
     * @param  string $message
     * @return void
     */
    public function fail(string $severity, string $message) {

        if ($this->fieldNamePretty) {
            $field = $this->fieldNamePretty;
        } else {
            $field = $this->field;
        }

        $field[0] = strtoupper($field[0]);
        $message = str_replace('{{field}}', $field, $message);
        $this->errorSeverity    = $severity;
        $this->errorMessage     = $message;
        $this->passed           = false;
    }

    /**
     * Get error
     *
     * @return array
     */
    public function getError(): array {
        return [
            'field'             => $this->field,
            'rule'              => $this->ruleType,
            'severity'          => $this->errorSeverity,
            'human_friendly'    => $this->errorMessage
        ];
    }

    /**
     * Reset properties affected by execution
     *
     * @return void
     */
    public function reset() {
        $this->passed       = false;
        $this->errorMessage = '';
    }

    /**
     * Execute rule on request input
     *
     * @param  array  $requestInput
     * @return void
     */
    abstract public function execute(array $requestInput);

}
