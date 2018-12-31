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

class Email extends Rule {

    /**
     * Execute rule on request input
     *
     * @param  array  $requestInput
     * @return void
     */
    public function execute(array $requestInput) {
        if (!filter_var($requestInput[$this->field], FILTER_VALIDATE_EMAIL)) {
            $this->fail(Rule::SEVERITY_DANGER, '"{{field}}" doesn\'t look to be properly formatted');
            return;
        }
        $this->pass();
    }

}
