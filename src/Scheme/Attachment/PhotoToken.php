<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

final readonly class PhotoToken implements \JsonSerializable
{
    public function __construct(public string $token = '')
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(token: (string)($data['token'] ?? ''));
    }

    public function jsonSerialize(): array
    {
        return ['token' => $this->token];
    }
}
