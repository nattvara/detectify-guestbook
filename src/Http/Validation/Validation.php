<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Guestbook\Http\Validation;

use Guestbook\Http\Request;
use Guestbook\Http\Validation\Rules\Rule;

class Validation {

    /**
     * Rules to execute on request
     *
     * @var array
     */
    private $rules = [];

    /**
     * Validation errors
     *
     * @var array
     */
    private $errors = [];

    /**
     * Add rule
     *
     * @param Rule $rule
     * @return void
     */
    public function addRule(Rule $rule) {
        $this->rules[] = $rule;
    }

    /**
     * Validate request
     *
     * @param  Request $request
     * @return bool
     */
    public function validate(Request $request): bool {
        $this->errors = [];
        foreach ($this->rules as $rule) {
            $rule->reset();
            $rule->execute($request->input());
            if ($rule->passed()) {
                continue;
            }
            $this->errors[] = $rule->getError();
        }
        if (empty($this->errors)) {
            return true;
        }
        return false;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors(): array {
        return $this->errors;
    }

}
