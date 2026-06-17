<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

final readonly class AttachmentPayload implements \JsonSerializable
{
    public function __construct(public string $url = '') {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(url: (string) ($data['url'] ?? ''));
    }

    public function jsonSerialize(): array
    {
        return ['url' => $this->url];
    }
}
