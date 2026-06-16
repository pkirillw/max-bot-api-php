<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

final readonly class UploadedInfo implements \JsonSerializable
{
    public function __construct(
        public int $fileId = 0,
        public string $token = '',
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            fileId: (int)($data['file_id'] ?? 0),
            token: (string)($data['token'] ?? ''),
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'file_id' => $this->fileId,
            'token' => $this->token,
        ], static fn(mixed $v) => $v !== null && $v !== 0 && $v !== '');
    }
}
