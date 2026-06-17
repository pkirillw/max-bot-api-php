<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;

final readonly class PhotoAttachment implements AttachmentInterface
{
    public function __construct(public PhotoAttachmentPayload $payload) {}

    public function getType(): AttachmentType
    {
        return AttachmentType::Image;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(payload: PhotoAttachmentPayload::fromJson((array) ($data['payload'] ?? [])));
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => AttachmentType::Image->value,
            'payload' => $this->payload->jsonSerialize(),
        ];
    }
}
