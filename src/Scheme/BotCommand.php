<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class BotCommand implements \JsonSerializable
{
    public function __construct(
        public string $name = '',
        public ?string $description = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            name: (string)($data['name'] ?? ''),
            description: $data['description'] ?? null,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
        ], static fn(mixed $v) => $v !== null && $v !== '');
    }
}
