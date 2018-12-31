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

class Required extends Rule {

    /**
     * Execute rule on request input
     *
     * @param  array  $requestInput
     * @return void
     */
    public function execute(array $requestInput) {
        if (!isset($requestInput[$this->field])) {
            $this->fail(Rule::SEVERITY_DANGER, '"{{field}}" is required and is currently missing');
            return;
        }
        if ($requestInput[$this->field] !== '') {
            $this->pass();
            return;
        }
        $this->fail(Rule::SEVERITY_DANGER, '"{{field}}" is required and is currently missing');
    }

}
