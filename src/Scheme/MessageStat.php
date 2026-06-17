<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class MessageStat implements \JsonSerializable
{
    public function __construct(public int $views = 0) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(views: (int) ($data['views'] ?? 0));
    }

    public function jsonSerialize(): array
    {
        return ['views' => $this->views];
    }
}
