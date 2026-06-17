<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

use Pkirillw\MaxBotApi\Scheme\Attachment\AttachmentInterface;
use Pkirillw\MaxBotApi\Scheme\Attachment\AttachmentParser;

final readonly class MessageBody implements \JsonSerializable
{
    /**
     * @param array<int, AttachmentInterface>           $attachments
     * @param list<array<string, mixed>>                $rawAttachments
     * @param list<MarkUp>                              $markups
     */
    public function __construct(
        public string $mid = '',
        public int $seq = 0,
        public string $text = '',
        public array $attachments = [],
        public array $rawAttachments = [],
        public string $replyTo = '',
        public array $markups = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        $rawAttachments = array_values($data['attachments'] ?? []);
        $attachments = [];
        foreach ($rawAttachments as $rawAttachment) {
            $attachment = AttachmentParser::fromJson((array) $rawAttachment);
            if ($attachment !== null) {
                $attachments[] = $attachment;
            }
        }
        $markups = [];
        foreach (($data['markup'] ?? []) as $markup) {
            $markups[] = MarkUp::fromJson((array) $markup);
        }
        return new self(
            mid: (string) ($data['mid'] ?? ''),
            seq: (int) ($data['seq'] ?? 0),
            text: (string) ($data['text'] ?? ''),
            attachments: $attachments,
            rawAttachments: $rawAttachments,
            replyTo: (string) ($data['reply_to'] ?? ''),
            markups: $markups,
        );
    }

    public function jsonSerialize(): array
    {
        $data = [
            'mid' => $this->mid,
            'seq' => $this->seq,
            'text' => $this->text,
            'attachments' => array_map(
                static fn(AttachmentInterface $a) => $a->jsonSerialize(),
                $this->attachments,
            ),
        ];
        if ($this->replyTo !== '') {
            $data['reply_to'] = $this->replyTo;
        }
        if ($this->markups !== []) {
            $data['markup'] = array_map(static fn(MarkUp $m) => $m->jsonSerialize(), $this->markups);
        }
        return $data;
    }
}
