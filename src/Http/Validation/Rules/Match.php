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

class Match extends Rule {

    /**
     * Key in input field must match
     *
     * @var string
     */
    private $key;

    /**
     * Pretty format of key used in error message
     *
     * @var string
     */
    private $keyPretty;

    /**
     * Execute rule on request input
     *
     * @param  array  $requestInput
     * @return void
     */
    public function execute(array $requestInput) {

        if ($requestInput[$this->field] !== $requestInput[$this->key]) {
            $this->fail(
                Rule::SEVERITY_DANGER,
                sprintf(
                    '"{{field}}" must match "%s"',
                    $this->keyPretty
                )
            );
            return;
        }

        $this->pass();
    }

    /**
     * Read options
     *
     * @param  array  $options
     * @return void
     */
    public function readOptions(array $options) {
        if (!in_array('key', array_keys($options))) {
            throw new ValidationException('\'key\' must be defined in options for match rule');
        }

        if (!in_array('key_pretty', array_keys($options))) {
            throw new ValidationException('\'key_pretty\' must be defined in options for match rule');
        }

        $this->key          = $options['key'];
        $this->keyPretty    = $options['key_pretty'];
    }

}
