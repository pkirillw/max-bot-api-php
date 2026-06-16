<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Exception;

/**
 * Wraps PSR-18 ClientExceptionInterface: trafinal nsport-level failures
 * (DNS, connection refused, TLS errors, etc.).
 */
class NetworkException extends MaxBotApiException
{
    public function __construct(public readonly string $operation, \Throwable $previous)
    {
        parent::__construct(
            sprintf('network error during %s: %s', $operation, $previous->getMessage()),
            0,
            $previous,
        );
    }
}
