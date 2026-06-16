<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;

final readonly class ContactAttachment implements AttachmentInterface
{
    public function __construct(public ContactAttachmentPayload $payload)
    {
    }

    public function getType(): AttachmentType
    {
        return AttachmentType::Contact;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(payload: ContactAttachmentPayload::fromJson((array)($data['payload'] ?? [])));
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => AttachmentType::Contact->value,
            'payload' => $this->payload->jsonSerialize(),
        ];
    }
}
