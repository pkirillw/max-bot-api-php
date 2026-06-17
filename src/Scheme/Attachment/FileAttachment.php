<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;

final readonly class FileAttachment implements AttachmentInterface
{
    public function __construct(
        public FileAttachmentPayload $payload,
        public string $filename = '',
        public int $size = 0,
    ) {}

    public function getType(): AttachmentType
    {
        return AttachmentType::File;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            payload: FileAttachmentPayload::fromJson((array) ($data['payload'] ?? [])),
            filename: (string) ($data['filename'] ?? ''),
            size: (int) ($data['size'] ?? 0),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => AttachmentType::File->value,
            'payload' => $this->payload->jsonSerialize(),
            'filename' => $this->filename,
            'size' => $this->size,
        ];
    }
}
