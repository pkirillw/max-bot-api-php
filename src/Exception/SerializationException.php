<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Exception;

class SerializationException extends MaxBotApiException
{
    public function __construct(string $operation, string $type, \Throwable $previous)
    {
        parent::__construct(
            sprintf('serialization error during %s of %s: %s', $operation, $type, $previous->getMessage()),
            0,
            $previous,
        );
    }
}
