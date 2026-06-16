<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

final readonly class StickerAttachmentPayload implements \JsonSerializable
{
    public function __construct(
        public string $url = '',
        public string $code = '',
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            url: (string)($data['url'] ?? ''),
            code: (string)($data['code'] ?? ''),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'url' => $this->url,
            'code' => $this->code,
        ];
    }
}
