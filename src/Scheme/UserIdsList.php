<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class UserIdsList implements \JsonSerializable
{
    /**
     * @param list<int> $userIds
     */
    public function __construct(public array $userIds = []) {}

    public function jsonSerialize(): array
    {
        return ['user_ids' => $this->userIds];
    }
}
