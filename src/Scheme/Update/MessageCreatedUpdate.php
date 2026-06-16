<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Update;

use Pkirillw\MaxBotApi\Scheme\Enum\UpdateType;
use Pkirillw\MaxBotApi\Scheme\Message;

final readonly class MessageCreatedUpdate extends AbstractUpdate
{
    public const COMMAND_UNDEFINED = 'undefined';

    public function __construct(
        public Message $message,
        int $timestamp = 0,
        string $debugRaw = '',
    ) {
        parent::__construct($timestamp, $debugRaw);
    }

    public function getUpdateType(): UpdateType
    {
        return UpdateType::MessageCreated;
    }

    public function getUserId(): int
    {
        return $this->message->sender?->userId ?? 0;
    }

    public function getChatId(): int
    {
        return $this->message->recipient?->chatId ?? 0;
    }

    public function getText(): string
    {
        return $this->message->body?->text ?? '';
    }

    public function getCommand(): string
    {
        $text = $this->getText();
        if (!str_starts_with($text, '/')) {
            return self::COMMAND_UNDEFINED;
        }
        if (str_contains($text, ':')) {
            return explode(':', $text)[0];
        }
        return $text;
    }

    public function getParam(): string
    {
        $text = $this->getText();
        if (!str_starts_with($text, '/')) {
            return '';
        }
        if (str_contains($text, ':')) {
            return explode(':', $text, 2)[1] ?? '';
        }
        return '';
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data, string $debugRaw = ''): self
    {
        return new self(
            message: Message::fromJson((array)($data['message'] ?? [])),
            timestamp: (int)($data['timestamp'] ?? 0),
            debugRaw: $debugRaw,
        );
    }
}
