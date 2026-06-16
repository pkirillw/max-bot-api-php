<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class CallbackAnswer implements \JsonSerializable
{
    public function __construct(
        public ?NewMessageBody $message = null,
        public string $notification = '',
    ) {}

    public function jsonSerialize(): array
    {
        return array_filter([
            'message' => $this->message?->jsonSerialize(),
            'notification' => $this->notification,
        ], static fn(mixed $v) => $v !== null && $v !== '');
    }
}
