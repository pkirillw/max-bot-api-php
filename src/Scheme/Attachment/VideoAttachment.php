<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;

final readonly class VideoAttachment implements AttachmentInterface
{
    public function __construct(public MediaAttachmentPayload $payload) {}

    public function getType(): AttachmentType
    {
        return AttachmentType::Video;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(payload: MediaAttachmentPayload::fromJson((array) ($data['payload'] ?? [])));
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => AttachmentType::Video->value,
            'payload' => $this->payload->jsonSerialize(),
        ];
    }
}
