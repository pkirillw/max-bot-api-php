<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class Callback implements \JsonSerializable
{
    public function __construct(
        public int $timestamp = 0,
        public string $callbackId = '',
        public string $payload = '',
        public ?User $user = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            timestamp: (int)($data['timestamp'] ?? 0),
            callbackId: (string)($data['callback_id'] ?? ''),
            payload: (string)($data['payload'] ?? ''),
            user: isset($data['user']) ? User::fromJson((array)$data['user']) : null,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'timestamp' => $this->timestamp,
            'callback_id' => $this->callbackId,
            'payload' => $this->payload,
            'user' => $this->user?->jsonSerialize(),
        ], static fn(mixed $v) => $v !== null && $v !== '' && $v !== 0);
    }
}
