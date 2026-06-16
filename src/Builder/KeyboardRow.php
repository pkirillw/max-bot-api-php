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

/**
 * One row of an inline keyboard. Each add*() method returns $this so calls chain.
 */
final class KeyboardRow
{
    /** @var list<ButtonInterface> */
    private array $buttons = [];

    public function addButton(ButtonInterface $button): self
    {
        $this->buttons[] = $button;
        return $this;
    }

    public function addLink(string $text, string $url): self
    {
        $this->buttons[] = new LinkButton(text: $text, url: $url);
        return $this;
    }

    public function addCallback(string $text, string $payload, Intent $intent = Intent::Default): self
    {
        $this->buttons[] = new CallbackButton(
            text: $text,
            payload: $payload,
            intent: $intent,
        );
        return $this;
    }

    public function addContact(string $text): self
    {
        $this->buttons[] = new RequestContactButton(text: $text);
        return $this;
    }

    public function addGeolocation(string $text, bool $quick = false): self
    {
        $this->buttons[] = new RequestGeoLocationButton(text: $text, quick: $quick);
        return $this;
    }

    public function addOpenApp(string $text, string $app, string $payload = '', int $contactId = 0): self
    {
        $this->buttons[] = new OpenAppButton(
            text: $text,
            webApp: $app,
            payload: $payload,
            contactId: $contactId,
        );
        return $this;
    }

    public function addMessage(string $text): self
    {
        $this->buttons[] = new MessageButton(text: $text);
        return $this;
    }

    public function addClipboard(string $text, string $payload): self
    {
        $this->buttons[] = new ClipboardButton(text: $text, payload: $payload);
        return $this;
    }

    /**
     * @return list<ButtonInterface>
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }
}
