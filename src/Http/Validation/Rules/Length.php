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

class Length extends Rule {

    /**
     * @var int
     */
    private $min;

    /**
     * @var int
     */
    private $max;

    /**
     * Execute rule on request input
     *
     * @param  array  $requestInput
     * @return void
     */
    public function execute(array $requestInput) {

        if ($this->min) {
            if (strlen($requestInput[$this->field]) < $this->min) {
                $this->fail(
                    Rule::SEVERITY_DANGER,
                    sprintf(
                        '"{{field}}" is required to be longer than %d characters (currently %d)',
                        $this->min,
                        strlen($requestInput[$this->field])
                    )
                );
                return;
            }
        }

        if ($this->max) {
            if (strlen($requestInput[$this->field]) > $this->max) {
                $this->fail(
                    Rule::SEVERITY_DANGER,
                    sprintf(
                        '"{{field}}" is required to be shorter than %d characters (currently %d)',
                        $this->max,
                        strlen($requestInput[$this->field])
                    )
                );
                return;
            }
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
        if (in_array('min', array_keys($options))) {
            $this->min = $options['min'];
        }
        if (in_array('max', array_keys($options))) {
            $this->max = $options['max'];
        }
    }

}
