<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Enum;

enum MessageLinkType: string
{
    case Forward = 'forward';
    case Reply = 'reply';
}
