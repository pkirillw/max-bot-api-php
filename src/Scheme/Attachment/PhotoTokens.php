<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

final readonly class PhotoTokens implements \JsonSerializable
{
    /**
     * @param array<string, PhotoToken> $photos
     */
    public function __construct(public array $photos = [])
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        $photos = [];
        foreach (($data['photos'] ?? []) as $key => $photo) {
            $photos[(string)$key] = PhotoToken::fromJson((array)$photo);
        }
        return new self(photos: $photos);
    }

    public function jsonSerialize(): array
    {
        return [
            'photos' => array_map(
                static fn(PhotoToken $t) => $t->jsonSerialize(),
                $this->photos,
            ),
        ];
    }
}
