<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

use Pkirillw\MaxBotApi\Scheme\Button\ButtonInterface;

final readonly class Keyboard implements \JsonSerializable
{
    /**
     * @param array<int, array<int, ButtonInterface>> $buttons
     */
    public function __construct(public array $buttons = []) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        $rows = [];
        foreach (($data['buttons'] ?? []) as $row) {
            $rowButtons = [];
            foreach ($row as $buttonData) {
                $button = ButtonParser::fromJson((array)$buttonData);
                if ($button !== null) {
                    $rowButtons[] = $button;
                }
            }
            $rows[] = $rowButtons;
        }
        return new self(buttons: $rows);
    }

    public function jsonSerialize(): array
    {
        return [
            'buttons' => array_map(
                static fn(array $row) => array_map(
                    static fn(ButtonInterface $b) => $b->jsonSerialize(),
                    $row
                ),
                $this->buttons,
            ),
        ];
    }
}
