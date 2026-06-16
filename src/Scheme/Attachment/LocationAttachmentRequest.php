<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;

final readonly class LocationAttachmentRequest implements AttachmentRequestInterface
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    public function getType(): AttachmentType
    {
        return AttachmentType::Location;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => AttachmentType::Location->value,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
