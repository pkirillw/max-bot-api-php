<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;

final readonly class StickerAttachment implements AttachmentInterface
{
    public function __construct(
        public StickerAttachmentPayload $payload,
        public int $width = 0,
        public int $height = 0,
    ) {}

    public function getType(): AttachmentType
    {
        return AttachmentType::Sticker;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            payload: StickerAttachmentPayload::fromJson((array) ($data['payload'] ?? [])),
            width: (int) ($data['width'] ?? 0),
            height: (int) ($data['height'] ?? 0),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => AttachmentType::Sticker->value,
            'payload' => $this->payload->jsonSerialize(),
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
