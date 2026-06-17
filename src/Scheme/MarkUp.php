<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

use Pkirillw\MaxBotApi\Scheme\Enum\MarkupType;

final readonly class MarkUp implements \JsonSerializable
{
    public function __construct(
        public int $from = 0,
        public int $length = 0,
        public int $userId = 0,
        public ?MarkupType $type = null,
        public ?string $url = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            from: (int) ($data['from'] ?? 0),
            length: (int) ($data['length'] ?? 0),
            userId: (int) ($data['user_id'] ?? 0),
            type: isset($data['type']) ? MarkupType::from((string) $data['type']) : null,
            url: $data['url'] ?? null,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'from' => $this->from,
            'length' => $this->length,
            'user_id' => $this->userId,
            'type' => $this->type?->value,
            'url' => $this->url,
        ], static fn(mixed $v) => $v !== null && $v !== 0 && $v !== '');
    }
}
