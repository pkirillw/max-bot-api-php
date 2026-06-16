<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoAttachmentRequestPayload;

final readonly class BotPatch implements \JsonSerializable
{
    /**
     * @param list<BotCommand> $commands
     */
    public function __construct(
        public ?string $name = null,
        public ?string $username = null,
        public ?string $description = null,
        public ?array $commands = null,
        public ?PhotoAttachmentRequestPayload $photo = null,
    ) {}

    public function jsonSerialize(): array
    {
        $data = [];
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        if ($this->username !== null) {
            $data['username'] = $this->username;
        }
        if ($this->description !== null) {
            $data['description'] = $this->description;
        }
        // commands can legitimately be set to [] to clear them — emit it only when explicitly assigned.
        if ($this->commands !== null) {
            $data['commands'] = array_map(static fn(BotCommand $c) => $c->jsonSerialize(), $this->commands);
        }
        if ($this->photo !== null) {
            $data['photo'] = $this->photo->jsonSerialize();
        }
        return $data;
    }
}
