<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;

final readonly class FileAttachmentRequest implements AttachmentRequestInterface
{
    public function __construct(public UploadedInfo $payload) {}

    public function getType(): AttachmentType
    {
        return AttachmentType::File;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => AttachmentType::File->value,
            'payload' => $this->payload->jsonSerialize(),
        ];
    }
}
