<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Enum;

enum ButtonType: string
{
    case Link = 'link';
    case Callback = 'callback';
    case Contact = 'request_contact';
    case GeoLocation = 'request_geo_location';
    case OpenApp = 'open_app';
    case Message = 'message';
    case Clipboard = 'clipboard';
}
