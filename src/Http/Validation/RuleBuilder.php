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

use Guestbook\Http\Validation\Exceptions\ValidationException;
use Guestbook\Http\Validation\Rules\Rule;

class RuleBuilder {

    /**
     * Rule name to class map
     *
     * @var array
     */
    private static $rules = [
        'required'              => Rules\Required::class,
        'email'                 => Rules\Email::class,
        'length'                => Rules\Length::class,
        'match'                 => Rules\Match::class,
        'pwnedpasswords.com'    => Rules\Pwnedpasswords::class,
    ];

    /**
     * Make new rule
     *
     * @param  string      $field           field to apply rule on
     * @param  string      $ruleType        type of rule (see $rules property)
     * @param  array       $options         options for given rule
     * @param  string|null $prettyFieldName a prettier output name for field
     * @return Rule
     */
    public static function make(string $field, string $ruleType, array $options = [], ?string $prettyFieldName = null): Rule {
        if (!in_array($ruleType, array_keys(RuleBuilder::$rules))) {
            throw new ValidationException(sprintf('Unkown rule \'%s\'', $ruleType));
        }
        $rule = new RuleBuilder::$rules[$ruleType]($ruleType);
        $rule->setField($field);
        $rule->readOptions($options);
        if ($prettyFieldName) {
            $rule->setPrettyFieldName($prettyFieldName);
        }
        return $rule;
    }

}
