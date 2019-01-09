<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Guestbook\Http;

use Guestbook\Helpers\Database;
use Guestbook\Http\Request;
use Guestbook\Http\Responses\Response;
use Guestbook\Http\Routes\GET;
use \ReflectionClass;

class Log {

    /**
     * Log http request and response
     *
     * @param  Request  $request
     * @param  Response $response
     * @return void
     */
    public static function http(Request $request, Response $response) {
        $db     = Database::getPDOConnection();
        $stmt   = $db->prepare('
            INSERT INTO `requests` (
                `user_id`,
                `request`,
                `request_time`,
                `response`,
                `exception`,
                `user_agent`,
                `ip`,
                `requested_at`
            )
            VALUES (
                :user_id,
                :request,
                :request_time,
                :response,
                :exception,
                :user_agent,
                :ip,
                NOW()
            );
        ');
        if ($request->getError()) {
            $error = new ReflectionClass($request->getError());
            $error = $error->getShortName();
            $error = sprintf('%s: %s', $error, $request->getError()->getMessage());
        }
        $stmt->execute([
            'user_id'       => $request->hasAuthenticatedUser() ? $request->user()->getId() : null,
            'request'       => sprintf('%s %s', $request->getMethodAsString(), $request->getPath()),
            'request_time'  => round((microtime(true) - constant('REQUEST_START')) * 1000),
            'response'      => sprintf('%d -> %s', $response->getStatusCode(), $response->getContentType()),
            'exception'     => isset($error) ? $error : null,
            'user_agent'    => $request->getUserAgent(),
            'ip'            => $request->getIp(),
        ]);
    }
}
