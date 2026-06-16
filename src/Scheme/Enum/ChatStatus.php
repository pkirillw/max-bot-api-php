<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Enum;

enum ChatStatus: string
{
    case Active = 'active';
    case Removed = 'removed';
    case Left = 'left';
    case Closed = 'closed';
    case Suspended = 'suspended';
}
