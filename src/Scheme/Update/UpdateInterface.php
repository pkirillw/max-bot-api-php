<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Update;

use Pkirillw\MaxBotApi\Scheme\Enum\UpdateType;

interface UpdateInterface
{
    public function getUpdateType(): UpdateType;

    public function getTimestamp(): int;

    public function getUserId(): int;

    public function getChatId(): int;

    public function getDebugRaw(): string;

    /**
     */
    public function getUpdateTime(): ?\DateTimeImmutable;
}
