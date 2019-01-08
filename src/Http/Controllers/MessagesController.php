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
        return MessagesController::formatMessages($request, $messages);

    }

    /**
     * Format messages
     *
     * @param  Request      $request
     * @param  array        $messages
     * @param  Message|null $startAt
     * @return JsonResponse
     */
    public static function formatMessages(Request $request, array $messages, ?Message $startAt = null): JsonResponse {

        $out = [];

        /**
         * Format message for output to client
         *
         * @param Message $message
         * @return array
         */
        $format = function(Message $message) use (&$request): array {
            if ($request->hasAuthenticatedUser()) {
                return $message->formatForClient($request->user());
            } else {
                return $message->formatForClient();
            }
        };

        /**
         * Add message to output
         *
         * @param Message $message
         * @return void
         */
        $add = function(Message $message, int $key) use (&$out, &$messages, $format) {
            $out[] = $format($message);
            unset($messages[$key]);
        };

        /**
         * Recursively add message to correct parent in output
         *
         * @param Message $message
         * @param array   $messages
         * @return array
         */
        $recursiveAdd = function(Message $message, array $messages) use ($format, &$recursiveAdd) {
            foreach ($messages as $key => $msg) {
                if ($msg['id'] === $message->getParentMessage()->getPublicId()) {
                    $messages[$key]['children'][] = $format($message);
                    break;
                }
                $messages[$key]['children'] = $recursiveAdd($message, $msg['children']);
            }
            return $messages;
        };

        foreach ($messages as $key => $message) {
            if (!$message->hasParentMessage()) {
                $add($message, $key);
            }
            if ($startAt) {
                if ($message->getId() === $startAt->getId()) {
                    $add($message, $key);
                }
            }
        }

        while (!empty($messages)) {
            foreach ($messages as $key => $message) {
                $out = $recursiveAdd($message, $out);
                unset($messages[$key]);
            }
        }

        return new JsonResponse([
            'messages'      => $out,
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
