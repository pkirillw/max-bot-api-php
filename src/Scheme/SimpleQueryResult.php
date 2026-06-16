<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class SimpleQueryResult implements \JsonSerializable
{
    public function __construct(
        public bool $success = false,
        public ?string $message = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            success: (bool)($data['success'] ?? false),
            message: $data['message'] ?? null,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'success' => $this->success,
            'message' => $this->message,
        ], static fn(mixed $v) => $v !== null);
    }
}
