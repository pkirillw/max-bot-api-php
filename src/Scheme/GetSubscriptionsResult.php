<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class GetSubscriptionsResult implements \JsonSerializable
{
    /**
     * @param list<Subscription> $subscriptions
     */
    public function __construct(public array $subscriptions = [])
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        $subscriptions = [];
        foreach (($data['subscriptions'] ?? []) as $subscription) {
            $subscriptions[] = Subscription::fromJson((array)$subscription);
        }
        return new self(subscriptions: $subscriptions);
    }

    public function jsonSerialize(): array
    {
        return [
            'subscriptions' => array_map(static fn(Subscription $s) => $s->jsonSerialize(), $this->subscriptions),
        ];
    }
}
