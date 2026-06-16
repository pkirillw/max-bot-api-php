<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Enum;

enum UploadType: string
{
    case Photo = 'image';
    case Video = 'video';
    case Audio = 'audio';
    case File = 'file';
}
