<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class User implements \JsonSerializable
{
    public function __construct(
        public int $userId = 0,
        public string $name = '',
        public ?string $username = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public bool $isBot = false,
        public ?int $lastActivityTime = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            userId: (int)($data['user_id'] ?? 0),
            name: (string)($data['name'] ?? ''),
            username: $data['username'] ?? null,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            isBot: (bool)($data['is_bot'] ?? false),
            lastActivityTime: isset($data['last_activity_time']) ? (int)$data['last_activity_time'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'user_id' => $this->userId,
            'name' => $this->name,
            'username' => $this->username,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'is_bot' => $this->isBot,
            'last_activity_time' => $this->lastActivityTime,
        ], static fn(mixed $v) => $v !== null && $v !== '' && $v !== false);
    }
}
