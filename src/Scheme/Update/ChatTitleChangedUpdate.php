<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Update;

use Pkirillw\MaxBotApi\Scheme\Enum\UpdateType;
use Pkirillw\MaxBotApi\Scheme\User;

final readonly class ChatTitleChangedUpdate extends AbstractUpdate
{
    public function __construct(
        public int $chatId,
        public User $user,
        public string $title,
        int $timestamp = 0,
        string $debugRaw = '',
    ) {
        parent::__construct($timestamp, $debugRaw);
    }

    public function getUpdateType(): UpdateType
    {
        return UpdateType::ChatTitleChanged;
    }

    public function getUserId(): int
    {
        return $this->user->userId;
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data, string $debugRaw = ''): self
    {
        return new self(
            chatId: (int)($data['chat_id'] ?? 0),
            user: User::fromJson((array)($data['user'] ?? [])),
            title: (string)($data['title'] ?? ''),
            timestamp: (int)($data['timestamp'] ?? 0),
            debugRaw: $debugRaw,
        );
    }
}
