<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Enum;

enum MarkupType: string
{
    case User = 'user_mention';
    case Bot = 'bot_mention';
    case Strong = 'strong';
    case Emphasized = 'emphasized';
    case Monospaced = 'monospaced';
    case Link = 'link';
    case Strikethrough = 'strikethrough';
    case Underline = 'underline';
}
