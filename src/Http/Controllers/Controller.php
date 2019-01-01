<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Guestbook\Http\Controllers;

use Guestbook\Http\Controllers\Exceptions\InvalidCsrfException;
use Guestbook\Http\Exceptions\GuestException;
use Guestbook\Http\Exceptions\UnauthenticatedException;
use Guestbook\Http\Request;
use Guestbook\Http\Validation\RuleBuilder;

abstract class Controller {

    /**
     * Validation errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Guard a route with check for valid CSRF token
     *
     * @param  Request $request
     * @return void
     * @throws InvalidCsrfException
     */
    protected function validateCsrf(Request $request) {
        if (!$request->containsValidCsrfToken()) {
            throw new InvalidCsrfException('Invalid CSRF token');
        }
    }

    /**
     * Guard request for authenticated users
     *
     * @param  Request $request
     * @return void
     * @throws UnauthenticatedException if user is not authenticated
     */
    protected function guard(Request $request) {
        if (!$request->hasAuthenticatedUser()) {
            throw new UnauthenticatedException;
        }
    }

    /**
     * Only allow route for unauthenticated users
     *
     * @param  Request $request
     * @return void
     * @throws GuestException if user IS authenticated
     */
    protected function guest(Request $request) {
        if ($request->hasAuthenticatedUser()) {
            throw new GuestException;
        }
    }

    /**
     * Make new rule
     *
     * @param  Request     $request
     * @param  string      $field           field to apply rule on
     * @param  string      $ruleType        type of rule (see $rules property)
     * @param  array       $options         options for given rule
     * @param  string|null $prettyFieldName a prettier output name for field
     * @return Rule
     */
    protected function addValidationRule(Request $request, string $field, string $ruleType, array $options = [], ?string $prettyFieldName = null) {
        $request->getValidation()->addRule(RuleBuilder::make(
            $field,
            $ruleType,
            $options,
            $prettyFieldName
        ));
    }

    /**
     * Perform request validation
     *
     * @param  Request $request
     * @return bool
     */
    protected function validateRequest(Request $request): bool {
        $result         = $request->getValidation()->validate($request);
        $this->errors   = $request->getValidation()->getErrors();
        return $result;
    }

    /**
     * Format error array as html
     *
     * @param  array  $errors
     * @return string
     */
    protected function formatErrorsAsHtml(array $errors): string {
        $html = PHP_EOL;
        $html .= '<ul>';
        foreach ($errors as $error) {
            $error = htmlspecialchars($error['human_friendly'], ENT_QUOTES, 'UTF-8');
            $html .= sprintf('    <li>%s</li>%s', $error, PHP_EOL);
        }
        $html .= '</ul>';
        return $html;
    }

}
