<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Enum;

enum SenderAction: string
{
    case TypingOn = 'typing_on';
    case TypingOff = 'typing_off';
    case SendingPhoto = 'sending_photo';
    case SendingVideo = 'sending_video';
    case SendingAudio = 'sending_audio';
    case MarkSeen = 'mark_seen';
}
