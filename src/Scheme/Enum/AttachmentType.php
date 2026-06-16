<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Enum;

enum AttachmentType: string
{
    case Image = 'image';
    case Video = 'video';
    case Audio = 'audio';
    case File = 'file';
    case Contact = 'contact';
    case Sticker = 'sticker';
    case Share = 'share';
    case Location = 'location';
    case InlineKeyboard = 'inline_keyboard';
}
