<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;
use Pkirillw\MaxBotApi\Scheme\Keyboard;

final readonly class InlineKeyboardAttachment implements AttachmentInterface
{
    public function __construct(public Keyboard $payload)
    {
    }

    public function getType(): AttachmentType
    {
        return AttachmentType::InlineKeyboard;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(payload: Keyboard::fromJson((array)($data['payload'] ?? [])));
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => AttachmentType::InlineKeyboard->value,
            'payload' => $this->payload->jsonSerialize(),
        ];
    }
}
