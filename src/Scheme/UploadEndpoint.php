<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class UploadEndpoint implements \JsonSerializable
{
    public function __construct(
        public string $url = '',
        public string $token = '',
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            url: (string)($data['url'] ?? ''),
            token: (string)($data['token'] ?? ''),
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'url' => $this->url,
            'token' => $this->token,
        ], static fn(mixed $v) => $v !== '');
    }
}
