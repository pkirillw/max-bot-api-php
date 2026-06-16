<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoAttachmentRequestPayload;

final readonly class ChatPatch implements \JsonSerializable
{
    public function __construct(
        public ?PhotoAttachmentRequestPayload $icon = null,
        public ?string $title = null,
    ) {}

    public function jsonSerialize(): array
    {
        return array_filter([
            'icon' => $this->icon?->jsonSerialize(),
            'title' => $this->title,
        ], static fn(mixed $v) => $v !== null);
    }
}
