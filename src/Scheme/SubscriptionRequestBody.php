<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class SubscriptionRequestBody implements \JsonSerializable
{
    /**
     * @param list<string> $updateTypes
     */
    public function __construct(
        public string $url,
        public ?string $secret = null,
        public array $updateTypes = [],
        public ?string $version = null,
    ) {}

    public function jsonSerialize(): array
    {
        return array_filter([
            'secret' => $this->secret,
            'url' => $this->url,
            'update_types' => $this->updateTypes,
            'version' => $this->version,
        ], static fn(mixed $v) => $v !== null && $v !== '' && $v !== []);
    }
}
