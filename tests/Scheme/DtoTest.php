<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests\Scheme;

use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Scheme\BotCommand;
use Pkirillw\MaxBotApi\Scheme\BotInfo;
use Pkirillw\MaxBotApi\Scheme\BotPatch;
use Pkirillw\MaxBotApi\Scheme\Callback;
use Pkirillw\MaxBotApi\Scheme\CallbackAnswer;
use Pkirillw\MaxBotApi\Scheme\Chat;
use Pkirillw\MaxBotApi\Scheme\ChatList;
use Pkirillw\MaxBotApi\Scheme\ChatMember;
use Pkirillw\MaxBotApi\Scheme\ChatMembersList;
use Pkirillw\MaxBotApi\Scheme\ChatPatch;
use Pkirillw\MaxBotApi\Scheme\Enum\ChatStatus;
use Pkirillw\MaxBotApi\Scheme\Enum\ChatType;
use Pkirillw\MaxBotApi\Scheme\Enum\MarkupType;
use Pkirillw\MaxBotApi\Scheme\Enum\MessageLinkType;
use Pkirillw\MaxBotApi\Scheme\Error;
use Pkirillw\MaxBotApi\Scheme\GetSubscriptionsResult;
use Pkirillw\MaxBotApi\Scheme\Image;
use Pkirillw\MaxBotApi\Scheme\LinkedMessage;
use Pkirillw\MaxBotApi\Scheme\MarkUp;
use Pkirillw\MaxBotApi\Scheme\Message;
use Pkirillw\MaxBotApi\Scheme\MessageBody;
use Pkirillw\MaxBotApi\Scheme\MessageStat;
use Pkirillw\MaxBotApi\Scheme\NewMessageBody;
use Pkirillw\MaxBotApi\Scheme\NewMessageLink;
use Pkirillw\MaxBotApi\Scheme\PinMessageBody;
use Pkirillw\MaxBotApi\Scheme\Recipient;
use Pkirillw\MaxBotApi\Scheme\Results;
use Pkirillw\MaxBotApi\Scheme\SimpleQueryResult;
use Pkirillw\MaxBotApi\Scheme\Subscription;
use Pkirillw\MaxBotApi\Scheme\SubscriptionRequestBody;
use Pkirillw\MaxBotApi\Scheme\UpdateList;
use Pkirillw\MaxBotApi\Scheme\UploadEndpoint;
use Pkirillw\MaxBotApi\Scheme\User;
use Pkirillw\MaxBotApi\Scheme\UserIdsList;

final class DtoTest extends TestCase
{
    public function testBotCommandRoundTrip(): void
    {
        $cmd = BotCommand::fromJson(['name' => 'start', 'description' => 'begin']);
        self::assertSame('start', $cmd->name);
        self::assertSame('begin', $cmd->description);
        self::assertSame(['name' => 'start', 'description' => 'begin'], $cmd->jsonSerialize());
    }

    public function testBotCommandEmptySerializesOnlyName(): void
    {
        $cmd = new BotCommand(name: 'start');
        self::assertSame(['name' => 'start'], $cmd->jsonSerialize());
    }

    public function testBotInfoRoundTrip(): void
    {
        $info = BotInfo::fromJson([
            'user_id' => 42,
            'name' => 'Bot',
            'username' => 'thebot',
            'commands' => [['name' => 'start']],
            'description' => 'desc',
        ]);
        self::assertSame(42, $info->userId);
        self::assertCount(1, $info->commands);
        $encoded = $info->jsonSerialize();
        self::assertSame('Bot', $encoded['name']);
        self::assertSame('thebot', $encoded['username']);
        self::assertSame('desc', $encoded['description']);
    }

    public function testBotInfoOmitsEmpty(): void
    {
        $info = new BotInfo();
        self::assertSame([], $info->jsonSerialize());
    }

    public function testBotPatchAllFields(): void
    {
        $patch = new BotPatch(
            name: 'n',
            username: 'u',
            description: 'd',
            commands: [new BotCommand(name: 'c')],
        );
        $encoded = $patch->jsonSerialize();
        self::assertSame('n', $encoded['name']);
        self::assertSame('u', $encoded['username']);
        self::assertSame('d', $encoded['description']);
        self::assertSame([['name' => 'c']], $encoded['commands']);
    }

    public function testBotPatchEmptySerializesToEmpty(): void
    {
        $patch = new BotPatch();
        self::assertSame([], $patch->jsonSerialize());
    }

    public function testCallbackRoundTrip(): void
    {
        $cb = Callback::fromJson([
            'timestamp' => 1000,
            'callback_id' => 'cb-1',
            'payload' => 'payload-data',
            'user' => ['user_id' => 9, 'name' => 'Alice'],
        ]);
        self::assertSame(1000, $cb->timestamp);
        self::assertSame('cb-1', $cb->callbackId);
        self::assertSame('payload-data', $cb->payload);
        self::assertSame(9, $cb->user?->userId);
        $encoded = $cb->jsonSerialize();
        self::assertSame('cb-1', $encoded['callback_id']);
    }

    public function testCallbackAnswerWithMessage(): void
    {
        $answer = new CallbackAnswer(message: new NewMessageBody(text: 'hi'), notification: 'notify');
        $encoded = $answer->jsonSerialize();
        self::assertSame('notify', $encoded['notification']);
        self::assertSame('hi', $encoded['message']['text']);
    }

    public function testCallbackAnswerEmpty(): void
    {
        self::assertSame([], (new CallbackAnswer())->jsonSerialize());
    }

    public function testChatRoundTripAllFields(): void
    {
        $chat = Chat::fromJson([
            'chat_id' => 1,
            'type' => 'chat',
            'status' => 'active',
            'title' => 'Title',
            'icon' => ['url' => 'https://img'],
            'last_event_time' => 123,
            'participants_count' => 10,
            'owner_id' => 5,
            'participants' => ['1' => 100],
            'is_public' => true,
            'link' => 'https://chat',
            'description' => 'desc',
            'messages_count' => 200,
        ]);
        self::assertSame(1, $chat->chatId);
        self::assertSame(ChatType::Chat, $chat->type);
        self::assertSame(ChatStatus::Active, $chat->status);
        self::assertSame('Title', $chat->title);
        self::assertSame('https://img', $chat->icon?->url);
        self::assertSame(5, $chat->ownerId);
        self::assertSame(['1' => 100], $chat->participants);
        self::assertTrue($chat->isPublic);
        self::assertSame('https://chat', $chat->link);
        self::assertSame('desc', $chat->description);

        $encoded = $chat->jsonSerialize();
        self::assertSame(1, $encoded['chat_id']);
        self::assertSame('chat', $encoded['type']);
        self::assertSame('active', $encoded['status']);
        self::assertSame(123, $encoded['last_event_time']);
        self::assertSame(10, $encoded['participants_count']);
        self::assertTrue($encoded['is_public']);
        self::assertSame(200, $encoded['messages_count']);
        self::assertSame('Title', $encoded['title']);
        self::assertSame(['url' => 'https://img'], $encoded['icon']);
        self::assertSame(5, $encoded['owner_id']);
        self::assertSame(['1' => 100], $encoded['participants']);
        self::assertSame('https://chat', $encoded['link']);
        self::assertSame('desc', $encoded['description']);
    }

    public function testChatListRoundTrip(): void
    {
        $list = ChatList::fromJson([
            'chats' => [['chat_id' => 1], ['chat_id' => 2]],
            'marker' => 99,
        ]);
        self::assertCount(2, $list->chats);
        self::assertSame(1, $list->chats[0]->chatId);
        self::assertSame(99, $list->marker);
    }

    public function testChatMemberRoundTrip(): void
    {
        $member = ChatMember::fromJson([
            'user_id' => 7,
            'name' => 'Alice',
            'username' => 'al',
            'avatar_url' => 'avatar',
            'full_avatar_url' => 'full',
            'last_access_time' => 100,
            'is_owner' => true,
            'is_admin' => true,
            'is_bot' => true,
            'join_time' => 200,
            'permissions' => ['read_all_messages', 'change_chat_info'],
        ]);
        self::assertSame(7, $member->userId);
        self::assertTrue($member->isOwner);
        self::assertTrue($member->isAdmin);
        self::assertTrue($member->isBot);
        self::assertCount(2, $member->permissions);
        $encoded = $member->jsonSerialize();
        self::assertSame('al', $encoded['username']);
        self::assertSame(['read_all_messages', 'change_chat_info'], $encoded['permissions']);
    }

    public function testChatMemberSkipsUnknownPermissions(): void
    {
        $member = ChatMember::fromJson(['user_id' => 1, 'permissions' => ['unknown_perm']]);
        self::assertSame([], $member->permissions);
    }

    public function testChatMembersListRoundTrip(): void
    {
        $list = ChatMembersList::fromJson([
            'members' => [['user_id' => 1], ['user_id' => 2]],
            'marker' => 5,
        ]);
        self::assertCount(2, $list->members);
        self::assertSame(5, $list->marker);
    }

    public function testChatPatchWithTitle(): void
    {
        $patch = new ChatPatch(title: 'New title');
        self::assertSame(['title' => 'New title'], $patch->jsonSerialize());
    }

    public function testChatPatchEmpty(): void
    {
        self::assertSame([], (new ChatPatch())->jsonSerialize());
    }

    public function testErrorRoundTrip(): void
    {
        $err = Error::fromJson([
            'error' => 'err',
            'code' => 'some.code',
            'message' => 'msg',
            'existing_phone_numbers' => ['+7000', '+7001'],
            'results' => [['phone_number' => '+7000', 'status' => 'ok']],
        ]);
        self::assertSame('err', $err->errorText);
        self::assertSame('some.code', $err->code);
        self::assertSame('msg', $err->message);
        self::assertSame(['+7000', '+7001'], $err->numberExist);
        self::assertCount(1, $err->results);
        self::assertSame('+7000', $err->results[0]->phoneNumber);
    }

    public function testGetSubscriptionsResultRoundTrip(): void
    {
        $result = GetSubscriptionsResult::fromJson([
            'subscriptions' => [['url' => 'https://hook']],
        ]);
        self::assertCount(1, $result->subscriptions);
        self::assertSame('https://hook', $result->subscriptions[0]->url);
    }

    public function testImageRoundTrip(): void
    {
        $img = Image::fromJson(['url' => 'https://img']);
        self::assertSame('https://img', $img->url);
        self::assertSame(['url' => 'https://img'], $img->jsonSerialize());
    }

    public function testLinkedMessageRoundTrip(): void
    {
        $lm = LinkedMessage::fromJson([
            'type' => 'reply',
            'sender' => ['user_id' => 1],
            'chat_id' => 9,
            'message' => ['mid' => 'm-1', 'seq' => 0, 'text' => 'hello'],
        ]);
        self::assertSame(MessageLinkType::Reply, $lm->type);
        self::assertSame(1, $lm->sender?->userId);
        self::assertSame(9, $lm->chatId);
        self::assertSame('hello', $lm->message?->text);
    }

    public function testMarkUpRoundTrip(): void
    {
        $m = MarkUp::fromJson([
            'from' => 1,
            'length' => 2,
            'user_id' => 3,
            'type' => 'strong',
            'url' => 'https://x',
        ]);
        self::assertSame(1, $m->from);
        self::assertSame(2, $m->length);
        self::assertSame(3, $m->userId);
        self::assertSame(MarkupType::Strong, $m->type);
        self::assertSame('https://x', $m->url);
    }

    public function testMessageRoundTrip(): void
    {
        $msg = Message::fromJson([
            'sender' => ['user_id' => 1, 'name' => 'Alice'],
            'recipient' => ['chat_id' => 2],
            'timestamp' => 999,
            'body' => ['mid' => 'm-1', 'seq' => 0, 'text' => 'hi'],
            'stat' => ['views' => 5],
            'url' => 'https://msg',
        ]);
        self::assertSame(1, $msg->sender?->userId);
        self::assertSame(2, $msg->recipient?->chatId);
        self::assertSame(999, $msg->timestamp);
        self::assertSame('hi', $msg->body?->text);
        self::assertSame(5, $msg->stat?->views);
        self::assertSame('https://msg', $msg->url);
    }

    public function testMessageBodyRoundTripWithMarkup(): void
    {
        $body = MessageBody::fromJson([
            'mid' => 'm-1',
            'seq' => 7,
            'text' => 'hi @u',
            'attachments' => [],
            'markup' => [['from' => 0, 'length' => 2, 'type' => 'strong']],
            'reply_to' => 'm-0',
        ]);
        self::assertSame('m-1', $body->mid);
        self::assertSame(7, $body->seq);
        self::assertSame('hi @u', $body->text);
        self::assertSame('m-0', $body->replyTo);
        self::assertCount(1, $body->markups);
    }

    public function testMessageStatRoundTrip(): void
    {
        $stat = MessageStat::fromJson(['views' => 5]);
        self::assertSame(5, $stat->views);
        self::assertSame(['views' => 5], $stat->jsonSerialize());
    }

    public function testNewMessageBodyFullSerialization(): void
    {
        $body = new NewMessageBody(
            text: 'hi',
            link: new NewMessageLink(type: MessageLinkType::Reply, mid: 'm-0'),
            notify: true,
        );
        $encoded = $body->jsonSerialize();
        self::assertSame('hi', $encoded['text']);
        self::assertSame([], $encoded['attachments']);
        self::assertTrue($encoded['notify']);
        self::assertSame(['type' => 'reply', 'mid' => 'm-0'], $encoded['link']);
    }

    public function testNewMessageLinkRoundTrip(): void
    {
        $link = NewMessageLink::fromJson(['type' => 'forward', 'mid' => 'm-1']);
        self::assertSame(MessageLinkType::Forward, $link->type);
        self::assertSame('m-1', $link->mid);
        self::assertSame(['type' => 'forward', 'mid' => 'm-1'], $link->jsonSerialize());
    }

    public function testPinMessageBodyRoundTrip(): void
    {
        $body = new PinMessageBody(messageId: 'mid-1', notify: false);
        self::assertSame(['message_id' => 'mid-1', 'notify' => false], $body->jsonSerialize());
        $body2 = new PinMessageBody(messageId: 'mid-2');
        self::assertSame(['message_id' => 'mid-2'], $body2->jsonSerialize());
    }

    public function testRecipientRoundTrip(): void
    {
        $r = Recipient::fromJson(['chat_id' => 5, 'chat_type' => 'channel', 'user_id' => 9]);
        self::assertSame(5, $r->chatId);
        self::assertSame(ChatType::Channel, $r->chatType);
        self::assertSame(9, $r->userId);
    }

    public function testResultsRoundTrip(): void
    {
        $r = Results::fromJson(['phone_number' => '+7000', 'status' => 'ok']);
        self::assertSame('+7000', $r->phoneNumber);
        self::assertSame('ok', $r->status);
    }

    public function testSimpleQueryResultRoundTrip(): void
    {
        $r = SimpleQueryResult::fromJson(['success' => true, 'message' => 'done']);
        self::assertTrue($r->success);
        self::assertSame('done', $r->message);
        self::assertSame(['success' => true, 'message' => 'done'], $r->jsonSerialize());

        $r2 = SimpleQueryResult::fromJson(['success' => false]);
        self::assertSame(['success' => false], $r2->jsonSerialize());
    }

    public function testSubscriptionRoundTrip(): void
    {
        $sub = Subscription::fromJson([
            'url' => 'https://hook',
            'time' => 100,
            'secret' => 'shh',
            'update_types' => ['message_created'],
            'version' => '1.2.5',
        ]);
        self::assertSame('https://hook', $sub->url);
        self::assertSame(100, $sub->time);
        self::assertSame('shh', $sub->secret);
        self::assertSame(['message_created'], $sub->updateTypes);
        self::assertSame('1.2.5', $sub->version);
    }

    public function testSubscriptionRequestBodySerializesAll(): void
    {
        $body = new SubscriptionRequestBody(
            url: 'https://hook',
            secret: 'shh',
            updateTypes: ['message_created'],
            version: '1.2.5',
        );
        $encoded = $body->jsonSerialize();
        self::assertSame('https://hook', $encoded['url']);
        self::assertSame('shh', $encoded['secret']);
        self::assertSame(['message_created'], $encoded['update_types']);
        self::assertSame('1.2.5', $encoded['version']);
    }

    public function testSubscriptionRequestBodySkipsEmpty(): void
    {
        $body = new SubscriptionRequestBody(url: 'https://hook');
        $encoded = $body->jsonSerialize();
        self::assertSame(['url' => 'https://hook'], $encoded);
    }

    public function testUpdateListRoundTrip(): void
    {
        $list = UpdateList::fromJson([
            'updates' => [['type' => 'message_created'], ['foo' => 'bar']],
            'marker' => 5,
        ]);
        self::assertCount(2, $list->updates);
        self::assertSame(5, $list->marker);
        self::assertSame('message_created', $list->updates[0]['type']);
    }

    public function testUploadEndpointRoundTrip(): void
    {
        $ep = UploadEndpoint::fromJson(['url' => 'https://up', 'token' => 'tok']);
        self::assertSame('https://up', $ep->url);
        self::assertSame('tok', $ep->token);
        $encoded = $ep->jsonSerialize();
        self::assertSame('https://up', $encoded['url']);
        self::assertSame('tok', $encoded['token']);
    }

    public function testUploadEndpointEmpty(): void
    {
        $ep = new UploadEndpoint();
        self::assertSame([], $ep->jsonSerialize());
    }

    public function testUserRoundTrip(): void
    {
        $u = User::fromJson([
            'user_id' => 7,
            'name' => 'Alice',
            'username' => 'al',
            'first_name' => 'A',
            'last_name' => 'B',
            'is_bot' => false,
            'last_activity_time' => 100,
        ]);
        self::assertSame(7, $u->userId);
        self::assertSame('Alice', $u->name);
        self::assertSame('al', $u->username);
        self::assertSame('A', $u->firstName);
        self::assertSame('B', $u->lastName);
        self::assertFalse($u->isBot);
        self::assertSame(100, $u->lastActivityTime);
        $encoded = $u->jsonSerialize();
        self::assertArrayNotHasKey('is_bot', $encoded);
    }

    public function testUserIdsListSerializes(): void
    {
        $list = new UserIdsList([1, 2, 3]);
        self::assertSame(['user_ids' => [1, 2, 3]], $list->jsonSerialize());
    }
}
