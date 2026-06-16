<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class Results implements \JsonSerializable
{
    public function __construct(
        public string $phoneNumber = '',
        public string $status = '',
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            phoneNumber: (string)($data['phone_number'] ?? ''),
            status: (string)($data['status'] ?? ''),
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'phone_number' => $this->phoneNumber,
            'status' => $this->status,
        ], static fn(mixed $v) => $v !== null && $v !== '');
    }
}
