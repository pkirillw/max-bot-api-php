<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

use Pkirillw\MaxBotApi\Scheme\Enum\ChatType;

final readonly class Recipient implements \JsonSerializable
{
    public function __construct(
        public int $chatId = 0,
        public ?ChatType $chatType = null,
        public int $userId = 0,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            chatId: (int)($data['chat_id'] ?? 0),
            chatType: isset($data['chat_type']) ? ChatType::from((string)$data['chat_type']) : null,
            userId: (int)($data['user_id'] ?? 0),
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'chat_id' => $this->chatId,
            'chat_type' => $this->chatType?->value,
            'user_id' => $this->userId,
        ], static fn(mixed $v) => $v !== null && $v !== 0);
    }
}
