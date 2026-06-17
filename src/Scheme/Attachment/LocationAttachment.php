<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;

final readonly class LocationAttachment implements AttachmentInterface
{
    public function __construct(
        public float $latitude = 0.0,
        public float $longitude = 0.0,
    ) {}

    public function getType(): AttachmentType
    {
        return AttachmentType::Location;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            latitude: (float) ($data['latitude'] ?? 0.0),
            longitude: (float) ($data['longitude'] ?? 0.0),
        );
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
