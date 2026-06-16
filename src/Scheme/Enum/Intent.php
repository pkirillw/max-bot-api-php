<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Enum;

enum Intent: string
{
    case Positive = 'positive';
    case Negative = 'negative';
    case Default = 'default';
}
