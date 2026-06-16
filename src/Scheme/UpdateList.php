<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class UpdateList implements \JsonSerializable
{
    /**
     * @param list<array<string, mixed>> $updates
     */
    public function __construct(
        public array $updates = [],
        public ?int $marker = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            updates: array_values((array)($data['updates'] ?? [])),
            marker: isset($data['marker']) ? (int)$data['marker'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'updates' => $this->updates,
            'marker' => $this->marker,
        ];
    }
}
