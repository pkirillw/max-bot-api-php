<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class Message implements \JsonSerializable
{
    public function __construct(
        public ?User $sender = null,
        public ?Recipient $recipient = null,
        public int $timestamp = 0,
        public ?LinkedMessage $link = null,
        public ?MessageBody $body = null,
        public ?MessageStat $stat = null,
        public string $url = '',
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            sender: isset($data['sender']) ? User::fromJson((array) $data['sender']) : null,
            recipient: isset($data['recipient']) ? Recipient::fromJson((array) $data['recipient']) : null,
            timestamp: (int) ($data['timestamp'] ?? 0),
            link: isset($data['link']) ? LinkedMessage::fromJson((array) $data['link']) : null,
            body: isset($data['body']) ? MessageBody::fromJson((array) $data['body']) : null,
            stat: isset($data['stat']) ? MessageStat::fromJson((array) $data['stat']) : null,
            url: (string) ($data['url'] ?? ''),
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'sender' => $this->sender?->jsonSerialize(),
            'recipient' => $this->recipient?->jsonSerialize(),
            'timestamp' => $this->timestamp,
            'link' => $this->link?->jsonSerialize(),
            'body' => $this->body?->jsonSerialize(),
            'stat' => $this->stat?->jsonSerialize(),
            'url' => $this->url,
        ], static fn(mixed $v) => $v !== null && $v !== '' && $v !== 0);
    }
}
