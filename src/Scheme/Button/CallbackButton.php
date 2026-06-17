<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Button;

use Pkirillw\MaxBotApi\Scheme\Enum\ButtonType;
use Pkirillw\MaxBotApi\Scheme\Enum\Intent;

final readonly class CallbackButton implements ButtonInterface
{
    public function __construct(
        public string $text,
        public string $payload,
        public Intent $intent = Intent::Default,
    ) {}

    public function getType(): ButtonType
    {
        return ButtonType::Callback;
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
            text: (string) ($data['text'] ?? ''),
            payload: (string) ($data['payload'] ?? ''),
            intent: isset($data['intent']) ? Intent::from((string) $data['intent']) : Intent::Default,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => ButtonType::Callback->value,
            'text' => $this->text,
            'payload' => $this->payload,
            'intent' => $this->intent->value,
        ];
    }
}
