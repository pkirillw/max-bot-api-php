<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Endpoint;

use Pkirillw\MaxBotApi\Builder\Keyboard;
use Pkirillw\MaxBotApi\Builder\Message;
use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Exception\ApiException;
use Pkirillw\MaxBotApi\Scheme\CallbackAnswer;
use Pkirillw\MaxBotApi\Scheme\Message as SchemeMessage;
use Pkirillw\MaxBotApi\Scheme\MessageList;
use Pkirillw\MaxBotApi\Scheme\SimpleQueryResult;

/**
 * /messages endpoints — send, edit, delete, list, answer callbacks.
 *
 * Send/Edit implement automatic retry on the "attachment.not.ready" sentinel —
 * MAX returns this code while an upload is still being processed on the server.
 */
final readonly class Messages
{
    private const MAX_RETRIES = 3;

    public function __construct(private Client $client)
    {
    }

    public function newKeyboardBuilder(): Keyboard
    {
        return new Keyboard();
    }

    /**
     * @param list<string> $messageIds
     */
    public function getMessages(int $chatId = 0, array $messageIds = [], int $from = 0, int $to = 0, int $count = 0): MessageList
    {
        $query = array_filter([
            'chat_id' => $chatId > 0 ? $chatId : null,
            'message_ids' => $messageIds !== [] ? implode(',', $messageIds) : null,
            'from' => $from > 0 ? $from : null,
            'to' => $to > 0 ? $to : null,
            'count' => $count > 0 ? $count : null,
        ], static fn(mixed $v) => $v !== null);

        $data = $this->client->requestJson('GET', 'messages', $query);
        return MessageList::fromJson($data);
    }

    public function getMessage(string $messageId): SchemeMessage
    {
        $data = $this->client->requestJson('GET', 'messages/' . rawurlencode($messageId));
        return SchemeMessage::fromJson($data);
    }

    /**
     * Send the message. Returns the new message identifier (mid) on success.
     * Throws ApiException on non-retriable failures.
     */
    public function send(Message $message): void
    {
        $this->sendWithResult($message);
    }

    /**
     * Same as {@see send()} but returns the created message body (incl. mid).
     */
    public function sendWithResult(Message $message): SchemeMessage
    {
        $lastError = null;
        for ($attempt = 0; $attempt < self::MAX_RETRIES; $attempt++) {
            try {
                return $this->sendMessage($message);
            } catch (ApiException $e) {
                $lastError = $e;
                if (!$e->isAttachmentNotReady()) {
                    throw $e;
                }
                if ($attempt < self::MAX_RETRIES - 1) {
                    usleep((1 << $attempt) * 1_000_000);
                }
            }
        }
        throw $lastError;
    }

    public function editMessage(string $messageId, Message $message): void
    {
        $lastError = null;
        for ($attempt = 0; $attempt < self::MAX_RETRIES; $attempt++) {
            try {
                $this->editMessageOnce($messageId, $message);
                return;
            } catch (ApiException $e) {
                $lastError = $e;
                if (!$e->isAttachmentNotReady()) {
                    throw $e;
                }
                if ($attempt < self::MAX_RETRIES - 1) {
                    usleep((1 << $attempt) * 1_000_000);
                }
            }
        }
        throw $lastError;
    }

    public function deleteMessage(string $messageId): SimpleQueryResult
    {
        $data = $this->client->requestJson('DELETE', 'messages', ['message_id' => $messageId]);
        return SimpleQueryResult::fromJson($data);
    }

    public function answerOnCallback(string $callbackId, CallbackAnswer $answer): SimpleQueryResult
    {
        $data = $this->client->requestJson(
            'POST',
            'answers',
            ['callback_id' => $callbackId],
            $answer,
        );
        return SimpleQueryResult::fromJson($data);
    }

    /**
     * Check whether the message can be delivered (used with phone-number-based messages).
     */
    public function check(Message $message): bool
    {
        $result = $this->checkNumberExist($message);
        return $result !== [];
    }

    /**
     * @return list<string>
     */
    public function listExist(Message $message): array
    {
        return $this->checkNumberExist($message);
    }

    private function sendMessage(Message $message): SchemeMessage
    {
        $query = array_filter([
            'chat_id' => $message->getChatId() > 0 ? $message->getChatId() : null,
            'user_id' => $message->getUserId() > 0 ? $message->getUserId() : null,
            'disable_link_preview' => $message->isDisableLinkPreview() ? 'true' : null,
        ]);

        $data = $this->client->requestJson('POST', 'messages', $query, $message->getBody(), $message->isReset());
        return SchemeMessage::fromJson((array)($data['message'] ?? $data));
    }

    private function editMessageOnce(string $messageId, Message $message): void
    {
        $data = $this->client->requestJson(
            'PUT',
            'messages',
            ['message_id' => $messageId],
            $message->getBody(),
        );
        $result = SimpleQueryResult::fromJson($data);
        if (!$result->success) {
            throw new ApiException(
                httpCode: 0,
                apiCode: 'edit.failed',
                details: $result->message,
            );
        }
    }

    /**
     * @return list<string>
     */
    private function checkNumberExist(Message $message): array
    {
        $query = [];
        if ($message->isReset() && $message->getBody()->botToken !== '') {
            $query['access_token'] = $message->getBody()->botToken;
        }
        if ($message->getBody()->phoneNumbers !== []) {
            $query['phone_numbers'] = implode(',', $message->getBody()->phoneNumbers);
        }

        $data = $this->client->requestJson('GET', 'notify/exists', $query, null, $message->isReset());
        return array_map('strval', (array)($data['existing_phone_numbers'] ?? []));
    }
}
