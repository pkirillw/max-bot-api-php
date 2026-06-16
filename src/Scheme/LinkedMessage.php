<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

use Pkirillw\MaxBotApi\Scheme\Enum\MessageLinkType;

final readonly class LinkedMessage implements \JsonSerializable
{
    public function __construct(
        public ?MessageLinkType $type = null,
        public ?User $sender = null,
        public int $chatId = 0,
        public ?MessageBody $message = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            type: isset($data['type']) ? MessageLinkType::from((string)$data['type']) : null,
            sender: isset($data['sender']) ? User::fromJson((array)$data['sender']) : null,
            chatId: (int)($data['chat_id'] ?? 0),
            message: isset($data['message']) ? MessageBody::fromJson((array)$data['message']) : null,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'type' => $this->type?->value,
            'sender' => $this->sender?->jsonSerialize(),
            'chat_id' => $this->chatId,
            'message' => $this->message?->jsonSerialize(),
        ], static fn(mixed $v) => $v !== null && $v !== 0);
    }
}
