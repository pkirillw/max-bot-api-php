<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

use Pkirillw\MaxBotApi\Scheme\Enum\MessageLinkType;

final readonly class NewMessageLink implements \JsonSerializable
{
    public function __construct(
        public MessageLinkType $type,
        public string $mid,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            type: MessageLinkType::from((string) ($data['type'] ?? '')),
            mid: (string) ($data['mid'] ?? ''),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type->value,
            'mid' => $this->mid,
        ];
    }
}
