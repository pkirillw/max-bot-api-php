<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Button;

use Pkirillw\MaxBotApi\Scheme\Enum\ButtonType;

final readonly class MessageButton implements ButtonInterface
{
    public function __construct(public string $text) {}

    public function getType(): ButtonType
    {
        return ButtonType::Message;
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(text: (string)($data['text'] ?? ''));
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => ButtonType::Message->value,
            'text' => $this->text,
        ];
    }
}
