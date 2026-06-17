<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests;

use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Scheme\Enum\ButtonType;
use Pkirillw\MaxBotApi\Scheme\Enum\UpdateType;
use Pkirillw\MaxBotApi\Scheme\Update\MessageCallbackUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\MessageCreatedUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\UpdateParser;

/**
 * Smoke test for the webhook parser using the real payload from MAX_API_Real_Payloads_2026.md.
 */
final class UpdateParserTest extends TestCase
{
    public function testParsesMessageCreated(): void
    {
        $payload = <<<'JSON'
{
  "timestamp": 1739184000000,
  "message": {
    "recipient": {"chat_id": -100000000, "chat_type": "dialog", "user_id": 12345},
    "timestamp": 1739184000000,
    "body": {"mid": "mid.X", "seq": 0, "text": "Привет"},
    "sender": {"user_id": 54321, "first_name": "User_Name", "name": "User_Name"}
  },
  "update_type": "message_created"
}
JSON;

        $update = UpdateParser::fromJsonString($payload);
        self::assertInstanceOf(MessageCreatedUpdate::class, $update);
        self::assertSame(UpdateType::MessageCreated, $update->getUpdateType());
        self::assertSame('Привет', $update->getText());
        self::assertSame(-100000000, $update->getChatId());
        self::assertSame(54321, $update->getUserId());
    }

    public function testParsesMessageCallbackWithKeyboard(): void
    {
        $payload = <<<'JSON'
{
  "callback": {
    "timestamp": 1739184000000,
    "callback_id": "CB1",
    "user": {"user_id": 54321, "name": "User"},
    "payload": "new_campaign"
  },
  "message": {
    "recipient": {"chat_id": -100000000, "chat_type": "dialog", "user_id": 54321},
    "body": {
      "mid": "mid.X", "seq": 0, "text": "Меню",
      "attachments": [{
        "type": "inline_keyboard",
        "payload": {"buttons": [[{"payload": "new_campaign", "text": "Создать", "intent": "default", "type": "callback"}]]}
      }]
    }
  },
  "timestamp": 1739184000000,
  "update_type": "message_callback"
}
JSON;

        $update = UpdateParser::fromJsonString($payload);
        self::assertInstanceOf(MessageCallbackUpdate::class, $update);
        self::assertSame('new_campaign', $update->callback->payload);
        self::assertSame(-100000000, $update->getChatId());

        $keyboard = $update->message?->body?->attachments[0] ?? null;
        self::assertNotNull($keyboard);
        self::assertSame(ButtonType::Callback, $keyboard->payload->buttons[0][0]->getType());
    }

    public function testReturnsNullForUnknownType(): void
    {
        $update = UpdateParser::fromJsonString('{"update_type": "never_seen"}');
        self::assertNull($update);
    }
}
