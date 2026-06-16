<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Endpoint;

use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Scheme\NewMessageBody;
use Pkirillw\MaxBotApi\Scheme\Update\UpdateInterface;

/**
 * Convenience helper to echo update payloads or arbitrary error messages into
 * a designated debug chat. Disabled (no-op) until {@see withChatId()} is set.
 */
final class Debugs
{
    private ?int $chatId;

    public function __construct(private Client $client, ?int $chatId = null)
    {
        $this->chatId = $chatId;
    }

    public function withChatId(int $chatId): self
    {
        $this->chatId = $chatId;
        return $this;
    }

    public function getChatId(): ?int
    {
        return $this->chatId;
    }

    public function send(UpdateInterface $update): void
    {
        $this->sendText($update->getDebugRaw());
    }

    public function sendErr(\Throwable $error): void
    {
        $this->sendText($error->getMessage());
    }

    public function sendText(string $text): void
    {
        if ($this->chatId === null) {
            return;
        }
        $this->client->requestJson(
            'POST',
            'messages',
            ['chat_id' => $this->chatId],
            new NewMessageBody(text: $text),
        );
    }
}
