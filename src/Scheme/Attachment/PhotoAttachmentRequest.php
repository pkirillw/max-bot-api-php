<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;

final readonly class PhotoAttachmentRequest implements AttachmentRequestInterface
{
    public function __construct(public PhotoAttachmentRequestPayload $payload)
    {
    }

    public function getType(): AttachmentType
    {
        return AttachmentType::Image;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => AttachmentType::Image->value,
            'payload' => $this->payload->jsonSerialize(),
        ];
    }
}
