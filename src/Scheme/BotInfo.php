<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class BotInfo implements \JsonSerializable
{
    /**
     * @param list<BotCommand> $commands
     */
    public function __construct(
        public int $userId = 0,
        public string $name = '',
        public ?string $username = null,
        public ?string $avatarUrl = null,
        public ?string $fullAvatarUrl = null,
        public array $commands = [],
        public ?string $description = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        $commands = [];
        foreach (($data['commands'] ?? []) as $command) {
            $commands[] = BotCommand::fromJson((array) $command);
        }
        return new self(
            userId: (int) ($data['user_id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
            username: $data['username'] ?? null,
            avatarUrl: $data['avatar_url'] ?? null,
            fullAvatarUrl: $data['full_avatar_url'] ?? null,
            commands: $commands,
            description: $data['description'] ?? null,
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
            'commands' => array_map(static fn(BotCommand $c) => $c->jsonSerialize(), $this->commands),
            'description' => $this->description,
        ], static fn(mixed $v) => $v !== null && $v !== '' && $v !== 0 && $v !== []);
    }
}
