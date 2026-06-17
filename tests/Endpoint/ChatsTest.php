<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests\Endpoint;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Client\Options;
use Pkirillw\MaxBotApi\Endpoint\Chats;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoAttachmentRequestPayload;
use Pkirillw\MaxBotApi\Scheme\ChatPatch;
use Pkirillw\MaxBotApi\Scheme\Enum\SenderAction;
use Pkirillw\MaxBotApi\Scheme\PinMessageBody;
use Pkirillw\MaxBotApi\Scheme\UserIdsList;
use Pkirillw\MaxBotApi\Tests\Support\CaptureHttp;

final class ChatsTest extends TestCase
{
    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    private function chats(CaptureHttp $http): Chats
    {
        return new Chats(new Client('tok', Options::default(), $http, $this->factory, $this->factory));
    }

    public function testGetChatsWithDefaults(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"chats":[{"chat_id":1,"type":"dialog","status":"active"}],"marker":5}');
        $list = $this->chats($http)->getChats();

        self::assertSame('GET', $http->requests[0]->getMethod());
        self::assertSame('https://platform-api.max.ru/chats?v=1.2.5', (string) $http->requests[0]->getUri());
        self::assertCount(1, $list->chats);
        self::assertSame(1, $list->chats[0]->chatId);
        self::assertSame(5, $list->marker);
    }

    public function testGetChatsWithCountAndMarker(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"chats":[],"marker":42}');
        $this->chats($http)->getChats(count: 10, marker: 42);

        self::assertSame('https://platform-api.max.ru/chats?count=10&marker=42&v=1.2.5', (string) $http->requests[0]->getUri());
    }

    public function testGetChat(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"chat_id":99,"type":"chat","status":"active","title":"Foo"}');
        $chat = $this->chats($http)->getChat(99);

        self::assertSame('https://platform-api.max.ru/chats/99?v=1.2.5', (string) $http->requests[0]->getUri());
        self::assertSame(99, $chat->chatId);
        self::assertSame('Foo', $chat->title);
    }

    public function testGetChatMembership(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"user_id":7,"is_admin":true}');
        $member = $this->chats($http)->getChatMembership(99);

        self::assertSame('https://platform-api.max.ru/chats/99/members/me?v=1.2.5', (string) $http->requests[0]->getUri());
        self::assertSame(7, $member->userId);
        self::assertTrue($member->isAdmin);
    }

    public function testGetChatMembers(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"members":[{"user_id":1}],"marker":3}');
        $list = $this->chats($http)->getChatMembers(99, count: 5, marker: 2);

        self::assertSame('https://platform-api.max.ru/chats/99/members?count=5&marker=2&v=1.2.5', (string) $http->requests[0]->getUri());
        self::assertCount(1, $list->members);
        self::assertSame(3, $list->marker);
    }

    public function testGetSpecificChatMembers(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"members":[{"user_id":1},{"user_id":2}]}');
        $list = $this->chats($http)->getSpecificChatMembers(99, [1, 2, 3]);

        self::assertSame('https://platform-api.max.ru/chats/99/members?user_ids=1%2C2%2C3&v=1.2.5', (string) $http->requests[0]->getUri());
        self::assertCount(2, $list->members);
    }

    public function testGetChatAdmins(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"members":[{"user_id":9,"is_admin":true}]}');
        $list = $this->chats($http)->getChatAdmins(99);

        self::assertSame('https://platform-api.max.ru/chats/99/members/admins?v=1.2.5', (string) $http->requests[0]->getUri());
        self::assertTrue($list->members[0]->isAdmin);
    }

    public function testLeaveChat(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"success":true}');
        $result = $this->chats($http)->leaveChat(99);

        self::assertSame('DELETE', $http->requests[0]->getMethod());
        self::assertSame('https://platform-api.max.ru/chats/99/members/me?v=1.2.5', (string) $http->requests[0]->getUri());
        self::assertTrue($result->success);
    }

    public function testEditChat(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"chat_id":99,"title":"New"}');
        $chat = $this->chats($http)->editChat(99, new ChatPatch(title: 'New'));

        $req = $http->requests[0];
        self::assertSame('PATCH', $req->getMethod());
        self::assertSame('https://platform-api.max.ru/chats/99?v=1.2.5', (string) $req->getUri());
        self::assertJsonStringEqualsJsonString('{"title":"New"}', (string) $req->getBody());
        self::assertSame('New', $chat->title);
    }

    public function testEditChatWithIcon(): void
    {
        $payload = new PhotoAttachmentRequestPayload(token: 'tok-1');
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"chat_id":99,"title":"X"}');
        $this->chats($http)->editChat(99, new ChatPatch(icon: $payload, title: 'X'));

        self::assertJsonStringEqualsJsonString('{"icon":{"token":"tok-1"},"title":"X"}', (string) $http->requests[0]->getBody());
    }

    public function testAddMember(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"success":true}');
        $result = $this->chats($http)->addMember(99, new UserIdsList([1, 2]));

        $req = $http->requests[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://platform-api.max.ru/chats/99/members?v=1.2.5', (string) $req->getUri());
        self::assertJsonStringEqualsJsonString('{"user_ids":[1,2]}', (string) $req->getBody());
        self::assertTrue($result->success);
    }

    public function testRemoveMember(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"success":true}');
        $result = $this->chats($http)->removeMember(99, 7);

        $req = $http->requests[0];
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame('https://platform-api.max.ru/chats/99/members?user_id=7&v=1.2.5', (string) $req->getUri());
        self::assertTrue($result->success);
    }

    public function testSendAction(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"success":true}');
        $result = $this->chats($http)->sendAction(99, SenderAction::TypingOn);

        $req = $http->requests[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://platform-api.max.ru/chats/99/actions?v=1.2.5', (string) $req->getUri());
        self::assertJsonStringEqualsJsonString('{"action":"typing_on"}', (string) $req->getBody());
        self::assertTrue($result->success);
    }

    public function testPinMessage(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"success":true}');
        $result = $this->chats($http)->pinMessage(99, new PinMessageBody(messageId: 'mid-1', notify: true));

        $req = $http->requests[0];
        self::assertSame('PUT', $req->getMethod());
        self::assertSame('https://platform-api.max.ru/chats/99/pin?v=1.2.5', (string) $req->getUri());
        self::assertJsonStringEqualsJsonString('{"message_id":"mid-1","notify":true}', (string) $req->getBody());
        self::assertTrue($result->success);
    }
}
