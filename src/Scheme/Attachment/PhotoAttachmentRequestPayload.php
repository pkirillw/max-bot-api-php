<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

final readonly class PhotoAttachmentRequestPayload implements \JsonSerializable
{
    /**
     * @param array<string, PhotoToken> $photos
     */
    public function __construct(
        public string $url = '',
        public string $token = '',
        public array $photos = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        $photos = [];
        foreach (($data['photos'] ?? []) as $key => $photo) {
            $photos[(string)$key] = PhotoToken::fromJson((array)$photo);
        }
        return new self(
            url: (string)($data['url'] ?? ''),
            token: (string)($data['token'] ?? ''),
            photos: $photos,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'url' => $this->url,
            'token' => $this->token,
            'photos' => array_map(
                static fn(PhotoToken $t) => $t->jsonSerialize(),
                $this->photos,
            ),
        ], static fn(mixed $v) => $v !== null && $v !== '' && $v !== []);
    }
}
