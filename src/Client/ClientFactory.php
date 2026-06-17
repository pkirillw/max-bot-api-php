<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Client;

use Pkirillw\MaxBotApi\Exception\EmptyTokenException;
use Psr\Http\Client\ClientInterface as PsrHttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Convenience constructor for {@see Client}. Useful when the user already
 * has PSR-17/18 factories in scope — pass them in and get a ready client.
 */
final class ClientFactory
{
    public static function create(
        string $token,
        PsrHttpClientInterface $http,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        ?Options $options = null,
    ): Client {
        if ($token === '') {
            throw new EmptyTokenException();
        }
        return new Client(
            token: $token,
            options: $options ?? Options::default(),
            http: $http,
            requestFactory: $requestFactory,
            streamFactory: $streamFactory,
        );
    }
}
