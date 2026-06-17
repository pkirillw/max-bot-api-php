<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Button;

use Pkirillw\MaxBotApi\Scheme\Enum\ButtonType;

final readonly class RequestGeoLocationButton implements ButtonInterface
{
    public function __construct(
        public string $text,
        public bool $quick = false,
    ) {}

    public function getType(): ButtonType
    {
        return ButtonType::GeoLocation;
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            text: (string) ($data['text'] ?? ''),
            quick: (bool) ($data['quick'] ?? false),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => ButtonType::GeoLocation->value,
            'text' => $this->text,
            'quick' => $this->quick,
        ];
    }
}
