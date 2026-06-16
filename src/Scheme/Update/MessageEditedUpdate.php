<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Update;

use Pkirillw\MaxBotApi\Scheme\Enum\UpdateType;
use Pkirillw\MaxBotApi\Scheme\Message;

final readonly class MessageEditedUpdate extends AbstractUpdate
{
    public function __construct(
        public Message $message,
        int $timestamp = 0,
        string $debugRaw = '',
    ) {
        parent::__construct($timestamp, $debugRaw);
    }

    public function getUpdateType(): UpdateType
    {
        return UpdateType::MessageEdited;
    }

    public function getUserId(): int
    {
        return $this->message->sender?->userId ?? 0;
    }

    public function getChatId(): int
    {
        return $this->message->recipient?->chatId ?? 0;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data, string $debugRaw = ''): self
    {
        return new self(
            message: Message::fromJson((array)($data['message'] ?? [])),
            timestamp: (int)($data['timestamp'] ?? 0),
            debugRaw: $debugRaw,
        );
    }
}
