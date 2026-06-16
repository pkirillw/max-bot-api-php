<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

final readonly class PhotoAttachmentPayload implements \JsonSerializable
{
    public function __construct(
        public int $photoId = 0,
        public string $token = '',
        public string $url = '',
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            photoId: (int)($data['photo_id'] ?? 0),
            token: (string)($data['token'] ?? ''),
            url: (string)($data['url'] ?? ''),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'photo_id' => $this->photoId,
            'token' => $this->token,
            'url' => $this->url,
        ];
    }
}
