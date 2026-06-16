<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Update;

use Pkirillw\MaxBotApi\Scheme\Enum\UpdateType;

abstract readonly class AbstractUpdate implements UpdateInterface
{
    public function __construct(
        public int $timestamp = 0,
        public string $debugRaw = '',
    ) {}

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getDebugRaw(): string
    {
        return $this->debugRaw;
    }

    public function getUpdateTime(): ?\DateTimeImmutable
    {
        if ($this->timestamp <= 0) {
            return null;
        }
        // MAX передаёт миллисекунды.
        return (new \DateTimeImmutable())->setTimestamp((int)floor($this->timestamp / 1000));
    }

    abstract public function getUpdateType(): UpdateType;

    abstract public function getUserId(): int;

    abstract public function getChatId(): int;
}
