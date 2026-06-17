<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests\Scheme;

use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Scheme\Attachment\AudioAttachment;
use Pkirillw\MaxBotApi\Scheme\Attachment\ContactAttachment;
use Pkirillw\MaxBotApi\Scheme\Attachment\FileAttachment;
use Pkirillw\MaxBotApi\Scheme\Attachment\InlineKeyboardAttachment;
use Pkirillw\MaxBotApi\Scheme\Attachment\LocationAttachment;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoAttachmentRequestPayload;
use Pkirillw\MaxBotApi\Scheme\Attachment\ShareAttachment;
use Pkirillw\MaxBotApi\Scheme\Attachment\StickerAttachment;
use Pkirillw\MaxBotApi\Scheme\Attachment\VideoAttachment;
use Pkirillw\MaxBotApi\Scheme\Chat;
use Pkirillw\MaxBotApi\Scheme\ChatList;
use Pkirillw\MaxBotApi\Scheme\ChatMember;
use Pkirillw\MaxBotApi\Scheme\ChatMembersList;
use Pkirillw\MaxBotApi\Scheme\Error;
use Pkirillw\MaxBotApi\Scheme\GetSubscriptionsResult;
use Pkirillw\MaxBotApi\Scheme\LinkedMessage;
use Pkirillw\MaxBotApi\Scheme\Message;
use Pkirillw\MaxBotApi\Scheme\MessageBody;
use Pkirillw\MaxBotApi\Scheme\MessageList;
use Pkirillw\MaxBotApi\Scheme\Recipient;
use Pkirillw\MaxBotApi\Scheme\Results;
use Pkirillw\MaxBotApi\Scheme\Subscription;
use Pkirillw\MaxBotApi\Scheme\Update\MessageCallbackUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\MessageCreatedUpdate;
use Pkirillw\MaxBotApi\Scheme\UpdateList;
use Pkirillw\MaxBotApi\Scheme\User;

final class JsonSerializeTest extends TestCase
{
    public function testAttachmentJsonSerialize(): void
    {
        $audio = AudioAttachment::fromJson(['payload' => ['url' => 'u', 'token' => 't']]);
        self::assertSame(['type' => 'audio', 'payload' => ['url' => 'u', 'token' => 't']], $audio->jsonSerialize());

        $video = VideoAttachment::fromJson(['payload' => ['url' => 'u', 'token' => 't']]);
        self::assertSame(['type' => 'video', 'payload' => ['url' => 'u', 'token' => 't']], $video->jsonSerialize());

        $file = FileAttachment::fromJson(['payload' => ['url' => 'u', 'token' => 't'], 'filename' => 'f.bin', 'size' => 10]);
        self::assertSame([
            'type' => 'file', 'payload' => ['url' => 'u', 'token' => 't'], 'filename' => 'f.bin', 'size' => 10,
        ], $file->jsonSerialize());

        $contact = ContactAttachment::fromJson(['payload' => ['vcf_info' => 'v', 'hash' => 'h']]);
        self::assertSame([
            'type' => 'contact',
            'payload' => ['vcf_info' => 'v', 'max_info' => null, 'hash' => 'h'],
        ], $contact->jsonSerialize());

        $sticker = StickerAttachment::fromJson(['payload' => ['url' => 'u', 'code' => 'c'], 'width' => 100, 'height' => 200]);
        self::assertSame([
            'type' => 'sticker', 'payload' => ['url' => 'u', 'code' => 'c'], 'width' => 100, 'height' => 200,
        ], $sticker->jsonSerialize());

        $location = LocationAttachment::fromJson(['latitude' => 1.5, 'longitude' => 2.5]);
        self::assertSame([
            'type' => 'location', 'latitude' => 1.5, 'longitude' => 2.5,
        ], $location->jsonSerialize());

        $share = ShareAttachment::fromJson(['payload' => ['url' => 'u']]);
        self::assertSame(['type' => 'share', 'payload' => ['url' => 'u']], $share->jsonSerialize());

        $keyboard = new \Pkirillw\MaxBotApi\Scheme\Keyboard();
        $inlineKb = InlineKeyboardAttachment::fromJson(['payload' => ['buttons' => []]]);
        self::assertSame(['type' => 'inline_keyboard', 'payload' => ['buttons' => []]], $inlineKb->jsonSerialize());
    }

    public function testPhotoAttachmentRequestPayloadAllSerializers(): void
    {
        $withUrl = new PhotoAttachmentRequestPayload(url: 'https://img');
        self::assertSame(['url' => 'https://img'], $withUrl->jsonSerialize());

        $withPhotos = new PhotoAttachmentRequestPayload(photos: ['s' => new \Pkirillw\MaxBotApi\Scheme\Attachment\PhotoToken('tok')]);
        $encoded = $withPhotos->jsonSerialize();
        self::assertSame('tok', $encoded['photos']['s']['token']);
    }

    public function testChatListJsonSerialize(): void
    {
        $list = new ChatList(chats: [new Chat(chatId: 1)], marker: 5);
        $encoded = $list->jsonSerialize();
        self::assertSame(1, $encoded['chats'][0]['chat_id']);
        self::assertSame(5, $encoded['marker']);
    }

    public function testChatMembersListJsonSerialize(): void
    {
        $list = new ChatMembersList(members: [new ChatMember(userId: 1)], marker: 5);
        $encoded = $list->jsonSerialize();
        self::assertSame(1, $encoded['members'][0]['user_id']);
        self::assertSame(5, $encoded['marker']);
    }

    public function testErrorJsonSerialize(): void
    {
        $err = new Error(
            errorText: 'err',
            code: 'x.y',
            message: 'msg',
            numberExist: ['+7000'],
            results: [new Results(phoneNumber: '+7000', status: 'ok')],
        );
        $encoded = $err->jsonSerialize();
        self::assertSame('err', $encoded['error']);
        self::assertSame('x.y', $encoded['code']);
        self::assertSame('msg', $encoded['message']);
        self::assertSame(['+7000'], $encoded['existing_phone_numbers']);
        self::assertSame([['phone_number' => '+7000', 'status' => 'ok']], $encoded['results']);
    }

    public function testGetSubscriptionsResultJsonSerialize(): void
    {
        $result = new GetSubscriptionsResult(subscriptions: [new Subscription(url: 'https://h')]);
        $encoded = $result->jsonSerialize();
        self::assertSame('https://h', $encoded['subscriptions'][0]['url']);
    }

    public function testLinkedMessageJsonSerialize(): void
    {
        $lm = new LinkedMessage(
            type: \Pkirillw\MaxBotApi\Scheme\Enum\MessageLinkType::Reply,
            sender: new User(userId: 1, name: 'A'),
            chatId: 9,
        );
        $encoded = $lm->jsonSerialize();
        self::assertSame('reply', $encoded['type']);
        self::assertSame(1, $encoded['sender']['user_id']);
        self::assertSame(9, $encoded['chat_id']);
    }

    public function testMessageJsonSerialize(): void
    {
        $msg = new Message(
            sender: new User(userId: 1, name: 'A'),
            recipient: new Recipient(chatId: 2),
            timestamp: 100,
            url: 'https://msg',
        );
        $encoded = $msg->jsonSerialize();
        self::assertSame(1, $encoded['sender']['user_id']);
        self::assertSame(2, $encoded['recipient']['chat_id']);
        self::assertSame('https://msg', $encoded['url']);
    }

    public function testMessageBodyJsonSerializeWithMarkup(): void
    {
        $body = new MessageBody(
            mid: 'm-1',
            seq: 7,
            text: 'hi',
            replyTo: 'm-0',
            markups: [new \Pkirillw\MaxBotApi\Scheme\MarkUp(from: 0, length: 2, type: \Pkirillw\MaxBotApi\Scheme\Enum\MarkupType::Strong)],
        );
        $encoded = $body->jsonSerialize();
        self::assertSame('m-1', $encoded['mid']);
        self::assertSame(7, $encoded['seq']);
        self::assertSame('hi', $encoded['text']);
        self::assertSame('m-0', $encoded['reply_to']);
        self::assertSame([['length' => 2, 'type' => 'strong']], $encoded['markup']);
    }

    public function testMessageListJsonSerialize(): void
    {
        $list = new MessageList(messages: [new Message(timestamp: 100)]);
        $encoded = $list->jsonSerialize();
        self::assertSame([['timestamp' => 100]], $encoded['messages']);
    }

    public function testRecipientJsonSerialize(): void
    {
        $r = new Recipient(chatId: 5, userId: 9);
        $encoded = $r->jsonSerialize();
        self::assertSame(5, $encoded['chat_id']);
        self::assertSame(9, $encoded['user_id']);
    }

    public function testResultsJsonSerialize(): void
    {
        $r = new Results(phoneNumber: '+7000', status: 'ok');
        self::assertSame(['phone_number' => '+7000', 'status' => 'ok'], $r->jsonSerialize());
    }

    public function testSubscriptionJsonSerialize(): void
    {
        $sub = new Subscription(
            url: 'https://h',
            time: 100,
            secret: 'shh',
            updateTypes: ['message_created'],
            version: '1.0',
        );
        $encoded = $sub->jsonSerialize();
        self::assertSame('https://h', $encoded['url']);
        self::assertSame(100, $encoded['time']);
        self::assertSame('shh', $encoded['secret']);
        self::assertSame(['message_created'], $encoded['update_types']);
        self::assertSame('1.0', $encoded['version']);
    }

    public function testUpdateListJsonSerialize(): void
    {
        $list = new UpdateList(updates: [['foo' => 'bar']], marker: 5);
        $encoded = $list->jsonSerialize();
        self::assertSame([['foo' => 'bar']], $encoded['updates']);
        self::assertSame(5, $encoded['marker']);
    }

    public function testChatJsonSerializeOmitsEmptyOptionalFields(): void
    {
        $chat = new Chat(chatId: 1, type: \Pkirillw\MaxBotApi\Scheme\Enum\ChatType::Dialog, status: \Pkirillw\MaxBotApi\Scheme\Enum\ChatStatus::Active);
        $encoded = $chat->jsonSerialize();
        self::assertArrayNotHasKey('title', $encoded);
        self::assertArrayNotHasKey('icon', $encoded);
        self::assertArrayNotHasKey('link', $encoded);
        self::assertArrayNotHasKey('description', $encoded);
        self::assertArrayNotHasKey('owner_id', $encoded);
        self::assertArrayNotHasKey('participants', $encoded);
    }

    public function testMessageCreatedUpdateCommandAndParam(): void
    {
        $update = MessageCreatedUpdate::fromJson([
            'message' => [
                'body' => ['mid' => 'm', 'seq' => 0, 'text' => '/start'],
            ],
        ]);
        self::assertSame('/start', $update->getCommand());
        self::assertSame('', $update->getParam());
        self::assertNotSame(MessageCreatedUpdate::COMMAND_UNDEFINED, $update->getCommand());
    }

    public function testMessageCreatedUpdateCommandAndParamWithColon(): void
    {
        $update = MessageCreatedUpdate::fromJson([
            'message' => ['body' => ['mid' => 'm', 'seq' => 0, 'text' => '/do:arg']],
        ]);
        self::assertSame('/do', $update->getCommand());
        self::assertSame('arg', $update->getParam());
    }

    public function testMessageCreatedUpdateCommandUndefinedForNonSlashText(): void
    {
        $update = MessageCreatedUpdate::fromJson([
            'message' => ['body' => ['mid' => 'm', 'seq' => 0, 'text' => 'just text']],
        ]);
        self::assertSame(MessageCreatedUpdate::COMMAND_UNDEFINED, $update->getCommand());
        self::assertSame('', $update->getParam());
    }

    public function testMessageCallbackUpdateGetUserIdFromCallback(): void
    {
        $update = MessageCallbackUpdate::fromJson([
            'callback' => ['callback_id' => 'cb', 'user' => ['user_id' => 42, 'name' => 'X']],
        ]);
        self::assertSame(42, $update->getUserId());
        self::assertSame(0, $update->getChatId());
    }

    public function testMessageCallbackUpdateGetChatIdFromMessage(): void
    {
        $update = MessageCallbackUpdate::fromJson([
            'callback' => ['callback_id' => 'cb'],
            'message' => ['recipient' => ['chat_id' => 99]],
        ]);
        self::assertSame(99, $update->getChatId());
        self::assertSame(0, $update->getUserId());
    }
}
