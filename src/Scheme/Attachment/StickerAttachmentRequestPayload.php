<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

final readonly class StickerAttachmentRequestPayload implements \JsonSerializable
{
    public function __construct(public string $code = '') {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(code: (string) ($data['code'] ?? ''));
    }

    public function jsonSerialize(): array
    {
        return ['code' => $this->code];
    }
}
