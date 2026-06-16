<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Endpoint;

use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Scheme\GetSubscriptionsResult;
use Pkirillw\MaxBotApi\Scheme\SimpleQueryResult;
use Pkirillw\MaxBotApi\Scheme\SubscriptionRequestBody;

/**
 * /subscriptions endpoints — WebHook lifecycle.
 */
final readonly class Subscriptions
{
    public function __construct(private Client $client)
    {
    }

    public function getSubscriptions(): GetSubscriptionsResult
    {
        $data = $this->client->requestJson('GET', 'subscriptions');
        return GetSubscriptionsResult::fromJson($data);
    }

    /**
     * @param list<string> $updateTypes
     */
    public function subscribe(string $url, array $updateTypes, string $secret): SimpleQueryResult
    {
        $body = new SubscriptionRequestBody(
            url: $url,
            secret: $secret,
            updateTypes: $updateTypes,
            version: $this->client->getOptions()->version,
        );
        $data = $this->client->requestJson('POST', 'subscriptions', [], $body);
        return SimpleQueryResult::fromJson($data);
    }

    public function unsubscribe(string $url): SimpleQueryResult
    {
        $data = $this->client->requestJson('DELETE', 'subscriptions', ['url' => $url]);
        return SimpleQueryResult::fromJson($data);
    }
}
