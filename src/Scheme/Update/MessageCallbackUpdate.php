<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Update;

use Pkirillw\MaxBotApi\Scheme\Callback;
use Pkirillw\MaxBotApi\Scheme\Enum\UpdateType;
use Pkirillw\MaxBotApi\Scheme\Message;

final readonly class MessageCallbackUpdate extends AbstractUpdate
{
    public function __construct(
        public Callback $callback,
        public ?Message $message = null,
        int $timestamp = 0,
        string $debugRaw = '',
    ) {
        parent::__construct($timestamp, $debugRaw);
    }

    public function getUpdateType(): UpdateType
    {
        return UpdateType::MessageCallback;
    }

    public function getUserId(): int
    {
        return $this->callback->user?->userId ?? 0;
    }

    public function getChatId(): int
    {
        return $this->message?->recipient?->chatId ?? 0;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data, string $debugRaw = ''): self
    {
        return new self(
            callback: Callback::fromJson((array) ($data['callback'] ?? [])),
            message: isset($data['message']) ? Message::fromJson((array) $data['message']) : null,
            timestamp: (int) ($data['timestamp'] ?? 0),
            debugRaw: $debugRaw,
        );
    }
}
