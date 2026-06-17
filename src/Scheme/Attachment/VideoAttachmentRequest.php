<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;

final readonly class VideoAttachmentRequest implements AttachmentRequestInterface
{
    public function __construct(public UploadedInfo $payload) {}

    public function getType(): AttachmentType
    {
        return AttachmentType::Video;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => AttachmentType::Video->value,
            'payload' => $this->payload->jsonSerialize(),
        ];
    }
}
