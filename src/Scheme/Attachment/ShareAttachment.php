<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;

final readonly class ShareAttachment implements AttachmentInterface
{
    public function __construct(public AttachmentPayload $payload)
    {
    }

    public function getType(): AttachmentType
    {
        return AttachmentType::Share;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(payload: AttachmentPayload::fromJson((array)($data['payload'] ?? [])));
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => AttachmentType::Share->value,
            'payload' => $this->payload->jsonSerialize(),
        ];
    }
}
