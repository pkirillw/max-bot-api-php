<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Builder;

use Pkirillw\MaxBotApi\Scheme\Button\ButtonInterface;
use Pkirillw\MaxBotApi\Scheme\Button\CallbackButton;
use Pkirillw\MaxBotApi\Scheme\Button\ClipboardButton;
use Pkirillw\MaxBotApi\Scheme\Button\LinkButton;
use Pkirillw\MaxBotApi\Scheme\Button\MessageButton;
use Pkirillw\MaxBotApi\Scheme\Button\OpenAppButton;
use Pkirillw\MaxBotApi\Scheme\Button\RequestContactButton;
use Pkirillw\MaxBotApi\Scheme\Button\RequestGeoLocationButton;
use Pkirillw\MaxBotApi\Scheme\Enum\Intent;
use Pkirillw\MaxBotApi\Scheme\Keyboard as SchemeKeyboard;

/**
 * Fluent builder for {@see SchemeKeyboard}. Each row is built through {@see KeyboardRow}.
 *
 * Usage:
 *   $kb = (new Keyboard())
 *       ->addRow()
 *       ->addCallback('Hello', 'hello_payload')
 *       ->addLink('Docs', 'https://dev.max.ru')
 *       ->getKeyboard();
 */
final class Keyboard
{
    /** @var list<KeyboardRow> */
    private array $rows = [];

    public function addRow(): KeyboardRow
    {
        $row = new KeyboardRow();
        $this->rows[] = $row;
        return $row;
    }

    /**
     * @return list<KeyboardRow>
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    public function build(): SchemeKeyboard
    {
        $buttons = array_map(static fn(KeyboardRow $row) => $row->getButtons(), $this->rows);
        return new SchemeKeyboard(buttons: $buttons);
    }
}
