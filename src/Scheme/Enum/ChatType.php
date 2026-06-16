<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Enum;

enum ChatType: string
{
    case Dialog = 'dialog';
    case Chat = 'chat';
    case Channel = 'channel';
}
