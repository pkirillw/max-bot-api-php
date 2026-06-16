<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Button;

use Pkirillw\MaxBotApi\Scheme\Enum\ButtonType;

final readonly class OpenAppButton implements ButtonInterface
{
    public function __construct(
        public string $text,
        public string $webApp = '',
        public string $payload = '',
        public int $contactId = 0,
    ) {}

    public function getType(): ButtonType
    {
        return ButtonType::OpenApp;
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
            webApp: (string)($data['web_app'] ?? ''),
            payload: (string)($data['payload'] ?? ''),
            contactId: (int)($data['contact_id'] ?? 0),
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'type' => ButtonType::OpenApp->value,
            'text' => $this->text,
            'web_app' => $this->webApp,
            'payload' => $this->payload,
            'contact_id' => $this->contactId,
        ], static fn(mixed $v) => $v !== null && $v !== '' && $v !== 0);
    }
}
