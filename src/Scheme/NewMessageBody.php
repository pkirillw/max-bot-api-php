<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

use Pkirillw\MaxBotApi\Scheme\Attachment\AttachmentRequestInterface;
use Pkirillw\MaxBotApi\Scheme\Enum\Format;

final readonly class NewMessageBody implements \JsonSerializable
{
    /**
     * @param list<AttachmentRequestInterface> $attachments
     * @param list<string>                     $phoneNumbers
     * @param list<MarkUp>                     $markups
     */
    public function __construct(
        public string $text = '',
        public array $attachments = [],
        public ?NewMessageLink $link = null,
        public ?Format $format = null,
        public array $phoneNumbers = [],
        public bool $notify = false,
        public array $markups = [],
        public string $botToken = '',
    ) {}

    public function jsonSerialize(): array
    {
        $data = [
            'text' => $this->text,
            'attachments' => array_map(
                static fn(AttachmentRequestInterface $a) => $a->jsonSerialize(),
                $this->attachments,
            ),
            'notify' => $this->notify,
        ];
        if ($this->link !== null) {
            $data['link'] = $this->link->jsonSerialize();
        }
        if ($this->format !== null) {
            $data['format'] = $this->format->value;
        }
        if ($this->phoneNumbers !== []) {
            $data['phone_numbers'] = $this->phoneNumbers;
        }
        if ($this->markups !== []) {
            $data['markup'] = array_map(static fn(MarkUp $m) => $m->jsonSerialize(), $this->markups);
        }
        if ($this->botToken !== '') {
            $data['bot_token'] = $this->botToken;
        }
        return $data;
    }
}
