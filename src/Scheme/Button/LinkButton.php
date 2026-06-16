<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Button;

use Pkirillw\MaxBotApi\Scheme\Enum\ButtonType;

readonly class LinkButton implements ButtonInterface
{
    public function __construct(
        public string $text,
        public string $url,
    ) {}

    public function getType(): ButtonType
    {
        return ButtonType::Link;
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
            text: (string)($data['text'] ?? ''),
            url: (string)($data['url'] ?? ''),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => ButtonType::Link->value,
            'text' => $this->text,
            'url' => $this->url,
        ];
    }
}
