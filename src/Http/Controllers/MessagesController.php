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

use Guestbook\Http\Request;
use Guestbook\Http\Responses\JsonResponse;
use Guestbook\Models\Message;

class MessagesController extends Controller {

    /**
     * List all messages
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function all(Request $request): JsonResponse {
        $messages = Message::all();
        foreach ($messages as $key => $message) {
            $messages[$key] = $message->formatForClient();
        }
        return new JsonResponse([
            'messages'      => $messages,
            'message'       => '',
            'status_code'   => 200,
        ]);
    }
}
