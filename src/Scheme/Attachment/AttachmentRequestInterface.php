<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;

interface AttachmentRequestInterface extends \JsonSerializable
{
    public function getType(): AttachmentType;
}
