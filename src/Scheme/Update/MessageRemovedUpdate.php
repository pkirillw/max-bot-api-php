<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Update;

use Pkirillw\MaxBotApi\Scheme\Enum\UpdateType;

final readonly class MessageRemovedUpdate extends AbstractUpdate
{
    public function __construct(
        public string $messageId = '',
        public int $chatId = 0,
        public int $userId = 0,
        int $timestamp = 0,
        string $debugRaw = '',
    ) {
        parent::__construct($timestamp, $debugRaw);
    }

    public function getUpdateType(): UpdateType
    {
        return UpdateType::MessageRemoved;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data, string $debugRaw = ''): self
    {
        return new self(
            messageId: (string) ($data['message_id'] ?? ''),
            chatId: (int) ($data['chat_id'] ?? 0),
            userId: (int) ($data['user_id'] ?? 0),
            timestamp: (int) ($data['timestamp'] ?? 0),
            debugRaw: $debugRaw,
        );
    }
}
