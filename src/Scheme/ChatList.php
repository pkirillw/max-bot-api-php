<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class ChatList implements \JsonSerializable
{
    /**
     * @param list<Chat> $chats
     */
    public function __construct(
        public array $chats = [],
        public ?int $marker = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        $chats = [];
        foreach (($data['chats'] ?? []) as $chat) {
            $chats[] = Chat::fromJson((array) $chat);
        }
        return new self(
            chats: $chats,
            marker: isset($data['marker']) ? (int) $data['marker'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'chats' => array_map(static fn(Chat $c) => $c->jsonSerialize(), $this->chats),
            'marker' => $this->marker,
        ];
    }
}
