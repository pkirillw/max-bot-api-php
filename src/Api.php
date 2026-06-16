<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi;

use Psr\Http\Client\ClientInterface as PsrHttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Client\ClientFactory;
use Pkirillw\MaxBotApi\Client\Options;
use Pkirillw\MaxBotApi\Endpoint\Bots;
use Pkirillw\MaxBotApi\Endpoint\Chats;
use Pkirillw\MaxBotApi\Endpoint\Debugs;
use Pkirillw\MaxBotApi\Endpoint\Messages;
use Pkirillw\MaxBotApi\Endpoint\Subscriptions;
use Pkirillw\MaxBotApi\Endpoint\Uploads;
use Pkirillw\MaxBotApi\Exception\EmptyTokenException;

/**
 * Entry point of the MAX Bot API client.
 *
 * Construct via {@see self::create()} with PSR-17/18 factories, then access
 * endpoint groups through public properties (Bots, Chats, Messages, …).
 *
 * The class intentionally mirrors the Go SDK's surface area so users moving
 * between languages find the same method names and shapes.
 */
final class Api
{
    public readonly Bots $bots;
    public readonly Chats $chats;
    public readonly Debugs $debugs;
    public readonly Messages $messages;
    public readonly Subscriptions $subscriptions;
    public readonly Uploads $uploads;

    public function __construct(public readonly Client $client)
    {
        $this->bots = new Bots($client);
        $this->chats = new Chats($client);
        $this->debugs = new Debugs($client, $client->getOptions()->debugChatId);
        $this->messages = new Messages($client);
        $this->subscriptions = new Subscriptions($client);
        $this->uploads = new Uploads($client);
    }

    /**
     * Build an Api from PSR-17/18 factories. Convenience wrapper around ClientFactory.
     */
    public static function create(
        string $token,
        PsrHttpClientInterface $http,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        ?Options $options = null,
    ): self {
        if ($token === '') {
            throw new EmptyTokenException();
        }
        $client = ClientFactory::create(
            token: $token,
            http: $http,
            requestFactory: $requestFactory,
            streamFactory: $streamFactory,
            options: $options,
        );
        return new self($client);
    }

    /**
     * Apply new options (e.g. switch debug mode, change base URL) to the underlying client.
     */
    public function withOptions(Options $options): void
    {
        $this->client->setOptions($options);
    }
}
