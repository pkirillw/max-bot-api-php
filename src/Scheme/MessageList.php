<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class MessageList implements \JsonSerializable
{
    /**
     * @param list<Message> $messages
     */
    public function __construct(public array $messages = []) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        $messages = [];
        foreach (($data['messages'] ?? []) as $message) {
            $messages[] = Message::fromJson((array) $message);
        }
        return new self(messages: $messages);
    }

    public function jsonSerialize(): array
    {
        return [
            'messages' => array_map(static fn(Message $m) => $m->jsonSerialize(), $this->messages),
        ];
    }
}
