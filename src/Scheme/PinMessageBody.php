<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class PinMessageBody implements \JsonSerializable
{
    public function __construct(
        public string $messageId,
        public ?bool $notify = null,
    ) {}

    public function jsonSerialize(): array
    {
        return array_filter([
            'message_id' => $this->messageId,
            'notify' => $this->notify,
        ], static fn(mixed $v) => $v !== null);
    }
}
