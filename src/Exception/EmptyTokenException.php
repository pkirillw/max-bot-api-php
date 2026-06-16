<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Exception;

class EmptyTokenException extends MaxBotApiException
{
    public function __construct()
    {
        parent::__construct('bot token is empty');
    }
}
