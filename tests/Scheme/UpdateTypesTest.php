<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests\Scheme;

use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Scheme\Enum\UpdateType;
use Pkirillw\MaxBotApi\Scheme\Update\BotAddedToChatUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\BotRemovedFromChatUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\BotStartedUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\BotStopedFromChatUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\ChatTitleChangedUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\DialogClearedFromChatUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\DialogRemovedFromChatUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\MessageEditedUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\MessageRemovedUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\UpdateParser;
use Pkirillw\MaxBotApi\Scheme\Update\UserAddedToChatUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\UserRemovedFromChatUpdate;

final class UpdateTypesTest extends TestCase
{
    public function testMessageRemovedUpdate(): void
    {
        $update = MessageRemovedUpdate::fromJson([
            'message_id' => 'mid-1',
            'chat_id' => 42,
            'user_id' => 7,
            'timestamp' => 1000,
        ], 'raw');
        self::assertSame(UpdateType::MessageRemoved, $update->getUpdateType());
        self::assertSame('mid-1', $update->messageId);
        self::assertSame(42, $update->getChatId());
        self::assertSame(7, $update->getUserId());
        self::assertSame(1000, $update->getTimestamp());
        self::assertSame('raw', $update->getDebugRaw());
    }

    public function testMessageEditedUpdate(): void
    {
        $update = MessageEditedUpdate::fromJson([
            'message' => [
                'sender' => ['user_id' => 1, 'name' => 'A'],
                'recipient' => ['chat_id' => 9],
                'body' => ['mid' => 'm', 'seq' => 0, 'text' => 'edited'],
            ],
            'timestamp' => 2000,
        ]);
        self::assertSame(UpdateType::MessageEdited, $update->getUpdateType());
        self::assertSame('edited', $update->message->body?->text);
        self::assertSame(9, $update->getChatId());
        self::assertSame(1, $update->getUserId());
    }

    public function testBotAddedToChatUpdate(): void
    {
        $update = BotAddedToChatUpdate::fromJson([
            'chat_id' => 11,
            'user' => ['user_id' => 22, 'name' => 'X'],
            'timestamp' => 3000,
        ]);
        self::assertSame(UpdateType::BotAdded, $update->getUpdateType());
        self::assertSame(11, $update->getChatId());
        self::assertSame(22, $update->getUserId());
        self::assertSame(22, $update->user->userId);
    }

    public function testBotRemovedFromChatUpdate(): void
    {
        $update = BotRemovedFromChatUpdate::fromJson([
            'chat_id' => 11,
            'user' => ['user_id' => 33, 'name' => 'Y'],
        ]);
        self::assertSame(UpdateType::BotRemoved, $update->getUpdateType());
        self::assertSame(11, $update->getChatId());
        self::assertSame(33, $update->getUserId());
    }

    public function testBotStopedFromChatUpdate(): void
    {
        $update = BotStopedFromChatUpdate::fromJson([
            'chat_id' => 11,
            'user' => ['user_id' => 44, 'name' => 'Z'],
        ]);
        self::assertSame(UpdateType::BotStoped, $update->getUpdateType());
        self::assertSame(11, $update->getChatId());
        self::assertSame(44, $update->getUserId());
    }

    public function testDialogClearedFromChatUpdate(): void
    {
        $update = DialogClearedFromChatUpdate::fromJson([
            'chat_id' => 5,
            'user' => ['user_id' => 6, 'name' => 'D'],
        ]);
        self::assertSame(UpdateType::DialogCleared, $update->getUpdateType());
        self::assertSame(5, $update->getChatId());
        self::assertSame(6, $update->getUserId());
    }

    public function testDialogRemovedFromChatUpdate(): void
    {
        $update = DialogRemovedFromChatUpdate::fromJson([
            'chat_id' => 7,
            'user' => ['user_id' => 8, 'name' => 'D2'],
        ]);
        self::assertSame(UpdateType::DialogRemoved, $update->getUpdateType());
        self::assertSame(7, $update->getChatId());
        self::assertSame(8, $update->getUserId());
    }

    public function testUserAddedToChatUpdate(): void
    {
        $update = UserAddedToChatUpdate::fromJson([
            'chat_id' => 100,
            'user' => ['user_id' => 101, 'name' => 'U'],
            'inviter_id' => 200,
        ]);
        self::assertSame(UpdateType::UserAdded, $update->getUpdateType());
        self::assertSame(100, $update->getChatId());
        self::assertSame(101, $update->getUserId());
        self::assertSame(200, $update->inviterId);
    }

    public function testUserRemovedFromChatUpdate(): void
    {
        $update = UserRemovedFromChatUpdate::fromJson([
            'chat_id' => 100,
            'user' => ['user_id' => 102, 'name' => 'V'],
            'admin_id' => 300,
        ]);
        self::assertSame(UpdateType::UserRemoved, $update->getUpdateType());
        self::assertSame(100, $update->getChatId());
        self::assertSame(102, $update->getUserId());
        self::assertSame(300, $update->adminId);
    }

    public function testBotStartedUpdate(): void
    {
        $update = BotStartedUpdate::fromJson([
            'chat_id' => 42,
            'user' => ['user_id' => 7, 'name' => 'Starter'],
            'payload' => '/start',
        ]);
        self::assertSame(UpdateType::BotStarted, $update->getUpdateType());
        self::assertSame(42, $update->getChatId());
        self::assertSame(7, $update->getUserId());
        self::assertSame('/start', $update->payload);
    }

    public function testChatTitleChangedUpdate(): void
    {
        $update = ChatTitleChangedUpdate::fromJson([
            'chat_id' => 42,
            'user' => ['user_id' => 7, 'name' => 'Renamer'],
            'title' => 'New Title',
        ]);
        self::assertSame(UpdateType::ChatTitleChanged, $update->getUpdateType());
        self::assertSame(42, $update->getChatId());
        self::assertSame(7, $update->getUserId());
        self::assertSame('New Title', $update->title);
    }

    public function testUpdateParserReturnsNullForNonArrayJson(): void
    {
        $update = UpdateParser::fromJsonString('"not an object"');
        self::assertNull($update);
    }

    public function testUpdateParserFromJsonStringKeepsDebugRawWhenRequested(): void
    {
        $payload = '{"update_type":"bot_started","chat_id":1,"user":{"user_id":1,"name":"X"}}';
        $update = UpdateParser::fromJsonString($payload, keepDebugRaw: true);
        self::assertInstanceOf(BotStartedUpdate::class, $update);
        self::assertSame($payload, $update->getDebugRaw());
    }

    public function testGetUpdateTimeReturnsNullForZeroTimestamp(): void
    {
        $update = BotStartedUpdate::fromJson([
            'chat_id' => 42,
            'user' => ['user_id' => 7, 'name' => 'X'],
        ]);
        self::assertNull($update->getUpdateTime());
    }

    public function testGetUpdateTimeConvertsMilliseconds(): void
    {
        $update = BotStartedUpdate::fromJson([
            'chat_id' => 42,
            'user' => ['user_id' => 7, 'name' => 'X'],
            'timestamp' => 1739184000_000,
        ]);
        $time = $update->getUpdateTime();
        self::assertNotNull($time);
        self::assertSame(1739184000, $time->getTimestamp());
    }
}
