<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Enum;

enum UpdateType: string
{
    case MessageCallback = 'message_callback';
    case MessageCreated = 'message_created';
    case MessageRemoved = 'message_removed';
    case MessageEdited = 'message_edited';
    case BotAdded = 'bot_added';
    case BotRemoved = 'bot_removed';
    case BotStoped = 'bot_stopped';
    case DialogRemoved = 'dialog_removed';
    case DialogCleared = 'dialog_cleared';
    case UserAdded = 'user_added';
    case UserRemoved = 'user_removed';
    case BotStarted = 'bot_started';
    case ChatTitleChanged = 'chat_title_changed';
}
