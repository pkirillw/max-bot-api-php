<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

use Pkirillw\MaxBotApi\Scheme\Enum\ChatStatus;
use Pkirillw\MaxBotApi\Scheme\Enum\ChatType;

final readonly class Chat implements \JsonSerializable
{
    /**
     * @param array<string, int>|null $participants
     */
    public function __construct(
        public int $chatId = 0,
        public ?ChatType $type = null,
        public ?ChatStatus $status = null,
        public ?string $title = null,
        public ?Image $icon = null,
        public int $lastEventTime = 0,
        public int $participantsCount = 0,
        public int $ownerId = 0,
        public ?array $participants = null,
        public bool $isPublic = false,
        public ?string $link = null,
        public ?string $description = null,
        public int $messagesCount = 0,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            chatId: (int) ($data['chat_id'] ?? 0),
            type: isset($data['type']) ? ChatType::from((string) $data['type']) : null,
            status: isset($data['status']) ? ChatStatus::from((string) $data['status']) : null,
            title: $data['title'] ?? null,
            icon: isset($data['icon']) ? Image::fromJson((array) $data['icon']) : null,
            lastEventTime: (int) ($data['last_event_time'] ?? 0),
            participantsCount: (int) ($data['participants_count'] ?? 0),
            ownerId: (int) ($data['owner_id'] ?? 0),
            participants: isset($data['participants']) ? (array) $data['participants'] : null,
            isPublic: (bool) ($data['is_public'] ?? false),
            link: $data['link'] ?? null,
            description: $data['description'] ?? null,
            messagesCount: (int) ($data['messages_count'] ?? 0),
        );
    }

    public function jsonSerialize(): array
    {
        // Always-emit fields mirror Go's schema: chat_id, type, status, last_event_time,
        // participants_count, is_public, messages_count. Others are omitted when empty.
        $data = [
            'chat_id' => $this->chatId,
            'type' => $this->type?->value,
            'status' => $this->status?->value,
            'last_event_time' => $this->lastEventTime,
            'participants_count' => $this->participantsCount,
            'is_public' => $this->isPublic,
            'messages_count' => $this->messagesCount,
        ];
        if ($this->title !== null && $this->title !== '') {
            $data['title'] = $this->title;
        }
        if ($this->icon !== null) {
            $data['icon'] = $this->icon->jsonSerialize();
        }
        if ($this->ownerId !== 0) {
            $data['owner_id'] = $this->ownerId;
        }
        if ($this->participants !== null) {
            $data['participants'] = $this->participants;
        }
        if ($this->link !== null && $this->link !== '') {
            $data['link'] = $this->link;
        }
        if ($this->description !== null) {
            $data['description'] = $this->description;
        }
        return $data;
    }
}
