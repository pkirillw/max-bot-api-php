<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Exception;

class InvalidUrlException extends MaxBotApiException
{
    public function __construct(string $url)
    {
        parent::__construct(sprintf('invalid API URL: %s', $url));
    }
}
