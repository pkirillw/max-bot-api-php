<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;

final readonly class ContactAttachmentRequest implements AttachmentRequestInterface
{
    public function __construct(public ContactAttachmentRequestPayload $payload)
    {
    }

    public function getType(): AttachmentType
    {
        return AttachmentType::Contact;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => AttachmentType::Contact->value,
            'payload' => $this->payload->jsonSerialize(),
        ];
    }
}
