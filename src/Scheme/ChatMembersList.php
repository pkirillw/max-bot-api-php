<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class ChatMembersList implements \JsonSerializable
{
    /**
     * @param list<ChatMember> $members
     */
    public function __construct(
        public array $members = [],
        public ?int $marker = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        $members = [];
        foreach (($data['members'] ?? []) as $member) {
            $members[] = ChatMember::fromJson((array)$member);
        }
        return new self(
            members: $members,
            marker: isset($data['marker']) ? (int)$data['marker'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'members' => array_map(static fn(ChatMember $m) => $m->jsonSerialize(), $this->members),
            'marker' => $this->marker,
        ];
    }
}
