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
use Guestbook\Models\Exceptions\VotingException;
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
            if ($request->hasAuthenticatedUser()) {
                $messages[$key] = $message->formatForClient($request->user());
            } else {
                $messages[$key] = $message->formatForClient();
            }
        }
        return new JsonResponse([
            'messages'      => $messages,
            'message'       => '',
            'status_code'   => 200,
        ]);
    }

    /**
     * Validate text field of new message form
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function validateText(Request $request): JsonResponse {

        $this->guard($request);
        $this->validateCsrf($request);

        $this->addValidationRule($request, 'text', 'required');
        $this->addValidationRule($request, 'text', 'length', ['min' => 1, 'max' => 1000]);

        if (!$this->validateRequest($request)) {
            return (new JsonResponse([
                'status_code'   => 400,
                'message'       => 'failed validation',
                'errors'        => $this->errors
            ]))->withStatusCode(400);
        }

        return (new JsonResponse([
            'status_code'   => 200,
            'message'       => 'passed validation',
            'errors'        => false
        ]))->withStatusCode(200);
    }

    /**
     * Post a new message
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function newMessage(Request $request): JsonResponse {

        $this->guard($request);
        $this->validateCsrf($request);

        $this->addValidationRule($request, 'text', 'required');
        $this->addValidationRule($request, 'text', 'length', ['min' => 1, 'max' => 1000]);

        if (!$this->validateRequest($request)) {
            return (new JsonResponse([
                'status_code'   => 400,
                'message'       => 'failed validation',
                'errors'        => $this->errors
            ]))->withStatusCode(400);
        }

        $message = Message::create($request->input('text'), $request->user());

        return (new JsonResponse([
            'status_code'   => 201,
            'message'       => '',
            'created'       => $message->formatForClient()
        ]))->withStatusCode(201);
    }

    /**
     * Reply to a message
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function reply(Request $request): JsonResponse {

        $this->guard($request);
        $this->validateCsrf($request);

        $this->addValidationRule($request, 'text', 'required');
        $this->addValidationRule($request, 'text', 'length', ['min' => 1, 'max' => 1000]);

        if (!$this->validateRequest($request)) {
            return (new JsonResponse([
                'status_code'   => 400,
                'message'       => 'failed validation',
                'errors'        => $this->errors
            ]))->withStatusCode(400);
        }

        $parent     = Message::findByPublicId($request->urlVariable('id'));
        $message    = Message::create($request->input('text'), $request->user(), $parent);

        return (new JsonResponse([
            'status_code'   => 201,
            'message'       => '',
            'created'       => $message->formatForClient()
        ]))->withStatusCode(201);
    }

    /**
     * Upvote a message
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function upvote(Request $request): JsonResponse {

        $this->guard($request);
        $this->validateCsrf($request);

        try {
            $message = Message::findByPublicId($request->urlVariable('id'));
            $message->upvote($request->user());
        } catch (VotingException $e) {
            $error = $message->getAuthor()->getId() !== $request->user()->getId() ? 'Unkown error' : 'You cannot vote on your own message';
            return (new JsonResponse([
                'status_code'   => 500,
                'message'       => $error,
                'errors'        => []
            ]))->withStatusCode(500);
        }

        return (new JsonResponse([
            'status_code'   => 200,
            'message'       => '',
            'votes'         => $message->formatForClient($request->user())['votes']
        ]))->withStatusCode(200);
    }

    /**
     * Downvote a message
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function downvote(Request $request): JsonResponse {

        $this->guard($request);
        $this->validateCsrf($request);

        try {
            $message = Message::findByPublicId($request->urlVariable('id'));
            $message->downvote($request->user());
        } catch (VotingException $e) {
            $error = $message->getAuthor()->getId() !== $request->user()->getId() ? 'Unkown error' : 'You cannot vote on your own message';
            return (new JsonResponse([
                'status_code'   => 500,
                'message'       => $error,
                'errors'        => []
            ]))->withStatusCode(500);
        }

        return (new JsonResponse([
            'status_code'   => 200,
            'message'       => '',
            'votes'         => $message->formatForClient($request->user())['votes']
        ]))->withStatusCode(200);
    }
}