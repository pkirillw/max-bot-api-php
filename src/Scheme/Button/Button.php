<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Button;

use Pkirillw\MaxBotApi\Scheme\Enum\ButtonType;

final readonly class Button implements ButtonInterface
{
    public function __construct(
        public ButtonType $type,
        public string $text = '',
    ) {}

    public function getType(): ButtonType
    {
        return $this->type;
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
        return new self(
            type: ButtonType::from((string) ($data['type'] ?? '')),
            text: (string) ($data['text'] ?? ''),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type->value,
            'text' => $this->text,
        ];
    }
}
