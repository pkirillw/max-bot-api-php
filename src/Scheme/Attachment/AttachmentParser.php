<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;

/**
 * Parses raw attachment JSON into a concrete AttachmentInterface based on its type.
 */
final class AttachmentParser
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): ?AttachmentInterface
    {
        $type = AttachmentType::tryFrom((string) ($data['type'] ?? ''));
        return match ($type) {
            AttachmentType::Audio => AudioAttachment::fromJson($data),
            AttachmentType::Video => VideoAttachment::fromJson($data),
            AttachmentType::Image => PhotoAttachment::fromJson($data),
            AttachmentType::File => FileAttachment::fromJson($data),
            AttachmentType::Contact => ContactAttachment::fromJson($data),
            AttachmentType::Sticker => StickerAttachment::fromJson($data),
            AttachmentType::Location => LocationAttachment::fromJson($data),
            AttachmentType::Share => ShareAttachment::fromJson($data),
            AttachmentType::InlineKeyboard => InlineKeyboardAttachment::fromJson($data),
            default => null,
        };
    }
}
