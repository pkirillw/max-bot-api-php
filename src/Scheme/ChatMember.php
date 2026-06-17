<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

use Pkirillw\MaxBotApi\Scheme\Enum\ChatAdminPermission;

final readonly class ChatMember implements \JsonSerializable
{
    /**
     * @param list<ChatAdminPermission> $permissions
     */
    public function __construct(
        public int $userId = 0,
        public string $name = '',
        public ?string $username = null,
        public ?string $avatarUrl = null,
        public ?string $fullAvatarUrl = null,
        public int $lastAccessTime = 0,
        public bool $isOwner = false,
        public bool $isAdmin = false,
        public bool $isBot = false,
        public int $joinTime = 0,
        public array $permissions = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        $permissions = [];
        foreach (($data['permissions'] ?? []) as $permission) {
            $parsed = ChatAdminPermission::tryFrom((string) $permission);
            if ($parsed !== null) {
                $permissions[] = $parsed;
            }
        }
        return new self(
            userId: (int) ($data['user_id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
            username: $data['username'] ?? null,
            avatarUrl: $data['avatar_url'] ?? null,
            fullAvatarUrl: $data['full_avatar_url'] ?? null,
            lastAccessTime: (int) ($data['last_access_time'] ?? 0),
            isOwner: (bool) ($data['is_owner'] ?? false),
            isAdmin: (bool) ($data['is_admin'] ?? false),
            isBot: (bool) ($data['is_bot'] ?? false),
            joinTime: (int) ($data['join_time'] ?? 0),
            permissions: $permissions,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'user_id' => $this->userId,
            'name' => $this->name,
            'username' => $this->username,
            'avatar_url' => $this->avatarUrl,
            'full_avatar_url' => $this->fullAvatarUrl,
            'last_access_time' => $this->lastAccessTime,
            'is_owner' => $this->isOwner,
            'is_admin' => $this->isAdmin,
            'is_bot' => $this->isBot,
            'join_time' => $this->joinTime,
            'permissions' => array_map(static fn(ChatAdminPermission $p) => $p->value, $this->permissions),
        ], static fn(mixed $v) => $v !== null && $v !== 0 && $v !== '' && $v !== false && $v !== []);
    }
}
