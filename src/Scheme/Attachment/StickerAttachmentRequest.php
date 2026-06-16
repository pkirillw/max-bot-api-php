<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;

final readonly class StickerAttachmentRequest implements AttachmentRequestInterface
{
    public function __construct(public StickerAttachmentRequestPayload $payload)
    {
    }

    public function getType(): AttachmentType
    {
        return AttachmentType::Sticker;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => AttachmentType::Sticker->value,
            'payload' => $this->payload->jsonSerialize(),
        ];
    }
}
