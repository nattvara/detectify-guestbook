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
use Guestbook\Http\Responses\HtmlResponse;
use Guestbook\Http\Responses\JsonResponse;
use Guestbook\Http\Responses\Response;
use Guestbook\Models\Exceptions\MessageNotFoundException;
use Guestbook\Models\Message;

class GuestbookController extends Controller {

    /**
     * The index page
     *
     * @param  Request $request
     * @return HtmlResponse
     */
    public function index(Request $request): HtmlResponse {
        return (new HtmlResponse('index.html', [
            'authenticated' => $request->hasAuthenticatedUser() ? 'true' : 'false',
            'name'          => $request->hasAuthenticatedUser() ? $request->user()->getName() : false,
            'email'         => $request->hasAuthenticatedUser() ? $request->user()->getEmail() : false,
        ]))->withCsrfToken($request);
    }

    /**
     * View a message by id
     *
     * @param  Request $request
     * @return Response
     */
    public function viewMessage(Request $request): Response {

        $format = $request->getQueryParams('format');
        if (!$format) {
            $format = 'html';
        } else if ($format !== 'json') {
            $format = 'html';
        }

        try {
            $message = Message::findByPublicId($request->urlVariable('id'));
        } catch (MessageNotFoundException $e) {
            return (new HtmlResponse('404.html'))->withStatusCode(404);
        }

        if ($format === 'html') {
            return (new HtmlResponse('message.html', [
                'authenticated' => $request->hasAuthenticatedUser() ? 'true' : 'false',
                'message_id'    => $message->getPublicId(),
                'text'          => $message->getText(),
                'author'        => $message->getAuthor()->getName(),
                'name'          => $request->hasAuthenticatedUser() ? $request->user()->getName() : false,
                'email'         => $request->hasAuthenticatedUser() ? $request->user()->getEmail() : false,
            ]))->withCsrfToken($request);
        }

        $messages = Message::findAllStartingAtMessage($message);
        return MessagesController::formatMessages($request, $messages, $message);

    }
}
