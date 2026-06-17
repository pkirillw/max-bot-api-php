<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;
use Pkirillw\MaxBotApi\Scheme\Keyboard;

final readonly class InlineKeyboardAttachmentRequest implements AttachmentRequestInterface
{
    public function __construct(public Keyboard $payload) {}

    public function getType(): AttachmentType
    {
        return AttachmentType::InlineKeyboard;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => AttachmentType::InlineKeyboard->value,
            'payload' => $this->payload->jsonSerialize(),
        ];
    }
}
