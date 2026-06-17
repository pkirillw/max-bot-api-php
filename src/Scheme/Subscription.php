<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class Subscription implements \JsonSerializable
{
    /**
     * @param list<string> $updateTypes
     */
    public function __construct(
        public string $url = '',
        public int $time = 0,
        public ?string $secret = null,
        public array $updateTypes = [],
        public ?string $version = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            url: (string) ($data['url'] ?? ''),
            time: (int) ($data['time'] ?? 0),
            secret: $data['secret'] ?? null,
            updateTypes: array_values(array_map('strval', (array) ($data['update_types'] ?? []))),
            version: $data['version'] ?? null,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'secret' => $this->secret,
            'url' => $this->url,
            'time' => $this->time,
            'update_types' => $this->updateTypes,
            'version' => $this->version,
        ], static fn(mixed $v) => $v !== null && $v !== '' && $v !== 0 && $v !== []);
    }
}
