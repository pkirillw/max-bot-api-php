<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Exception;

final class TimeoutException extends MaxBotApiException
{
    public function __construct(public readonly string $operation, string $reason = '')
    {
        $message = $reason !== ''
            ? sprintf('timeout error during %s: %s', $operation, $reason)
            : sprintf('timeout error during %s', $operation);
        parent::__construct($message);
    }
}
