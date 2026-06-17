<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests\Endpoint;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Builder\Keyboard;
use Pkirillw\MaxBotApi\Builder\Message as MessageBuilder;
use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Client\Options;
use Pkirillw\MaxBotApi\Endpoint\Messages;
use Pkirillw\MaxBotApi\Exception\ApiException;
use Pkirillw\MaxBotApi\Scheme\CallbackAnswer;
use Pkirillw\MaxBotApi\Scheme\NewMessageBody;
use Pkirillw\MaxBotApi\Tests\Support\CaptureHttp;

final class MessagesTest extends TestCase
{
    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    private function messages(CaptureHttp $http): Messages
    {
        return new Messages(new Client('tok', Options::default(), $http, $this->factory, $this->factory));
    }

    public function testNewKeyboardBuilderReturnsKeyboard(): void
    {
        $messages = $this->messages(CaptureHttp::make($this->factory));
        $kb = $messages->newKeyboardBuilder();
        self::assertInstanceOf(Keyboard::class, $kb);
    }

    public function testGetMessagesWithFilters(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"messages":[{"body":{"mid":"m-1","seq":0,"text":"hi"}}]}');
        $list = $this->messages($http)->getMessages(chatId: 1, messageIds: ['m-1', 'm-2'], from: 100, to: 200, count: 10);

        self::assertSame('https://platform-api.max.ru/messages?chat_id=1&message_ids=m-1%2Cm-2&from=100&to=200&count=10&v=1.2.5', (string) $http->requests[0]->getUri());
        self::assertCount(1, $list->messages);
        self::assertSame('hi', $list->messages[0]->body?->text);
    }

    public function testGetMessage(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"body":{"mid":"mid.1","seq":0,"text":"hi"}}');
        $msg = $this->messages($http)->getMessage('mid.1');
        self::assertSame('https://platform-api.max.ru/messages/mid.1?v=1.2.5', (string) $http->requests[0]->getUri());
        self::assertSame('hi', $msg->body?->text);
    }

    public function testSendBuildsPostWithQuery(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"message":{"body":{"mid":"mid-X","seq":0,"text":"hi"}}}');
        $builder = MessageBuilder::new()
            ->setChat(42)
            ->setDisableLinkPreview(true)
            ->setText('hi');

        $this->messages($http)->send($builder);

        $req = $http->requests[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://platform-api.max.ru/messages?chat_id=42&disable_link_preview=true&v=1.2.5', (string) $req->getUri());
    }

    public function testSendUsesUserIdWhenChatIsZero(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"message":{"body":{"mid":"m","seq":0,"text":"x"}}}');
        $builder = MessageBuilder::new()->setUser(99)->setText('x');
        $this->messages($http)->send($builder);

        self::assertSame('https://platform-api.max.ru/messages?user_id=99&v=1.2.5', (string) $http->requests[0]->getUri());
    }

    public function testSendWithResultReturnsMessageWithMidAtTopLevel(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"body":{"mid":"top-mid","seq":0,"text":"hi"}}');
        $builder = MessageBuilder::new()->setChat(1)->setText('hi');
        $message = $this->messages($http)->sendWithResult($builder);
        self::assertSame('top-mid', $message->body?->mid);
    }

    public function testEditMessageUpdatesMessage(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"success":true}');
        $builder = MessageBuilder::new()->setChat(1)->setText('edited');
        $this->messages($http)->editMessage('mid-1', $builder);

        $req = $http->requests[0];
        self::assertSame('PUT', $req->getMethod());
        self::assertSame('https://platform-api.max.ru/messages?message_id=mid-1&v=1.2.5', (string) $req->getUri());
    }

    public function testEditMessageThrowsWhenServerReturnsSuccessFalse(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"success":false,"message":"too old"}');
        $builder = MessageBuilder::new()->setChat(1)->setText('edited');

        try {
            $this->messages($http)->editMessage('mid-1', $builder);
            self::fail('expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('edit.failed', $e->apiCode);
            self::assertSame('too old', $e->details);
        }
    }

    public function testEditMessageRetriesOnAttachmentNotReady(): void
    {
        $http = CaptureHttp::make($this->factory)
            ->enqueue(400, '{"code":"attachment.not.ready"}')
            ->enqueue(200, '{"success":true}');
        $builder = MessageBuilder::new()->setChat(1)->setText('edited');

        $start = microtime(true);
        $this->messages($http)->editMessage('mid-1', $builder);
        $elapsed = microtime(true) - $start;

        self::assertSame(2, $http->calls);
        self::assertGreaterThan(0.8, $elapsed, 'expected ~1s sleep');
    }

    public function testEditMessageGivesUpAfterMaxRetries(): void
    {
        $http = CaptureHttp::make($this->factory)
            ->enqueue(400, '{"code":"attachment.not.ready"}')
            ->enqueue(400, '{"code":"attachment.not.ready"}')
            ->enqueue(400, '{"code":"attachment.not.ready"}');

        try {
            $this->messages($http)->editMessage('mid-1', MessageBuilder::new()->setText('x'));
            self::fail('expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('attachment.not.ready', $e->apiCode);
            self::assertSame(3, $http->calls);
        }
    }

    public function testSendGivesUpAfterMaxRetries(): void
    {
        $http = CaptureHttp::make($this->factory);
        for ($i = 0; $i < 3; $i++) {
            $http->enqueue(400, '{"code":"attachment.not.ready"}');
        }

        try {
            $this->messages($http)->send(MessageBuilder::new()->setChat(1)->setText('x'));
            self::fail('expected ApiException');
        } catch (ApiException $e) {
            self::assertSame(3, $http->calls);
        }
    }

    public function testDeleteMessage(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"success":true}');
        $result = $this->messages($http)->deleteMessage('mid-1');
        self::assertSame('DELETE', $http->requests[0]->getMethod());
        self::assertSame('https://platform-api.max.ru/messages?message_id=mid-1&v=1.2.5', (string) $http->requests[0]->getUri());
        self::assertTrue($result->success);
    }

    public function testAnswerOnCallback(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"success":true}');
        $answer = new CallbackAnswer(message: new NewMessageBody(text: 'hi'), notification: 'note');
        $result = $this->messages($http)->answerOnCallback('cb-1', $answer);

        $req = $http->requests[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://platform-api.max.ru/answers?callback_id=cb-1&v=1.2.5', (string) $req->getUri());
        self::assertJsonStringEqualsJsonString('{"message":{"text":"hi","attachments":[],"notify":false},"notification":"note"}', (string) $req->getBody());
        self::assertTrue($result->success);
    }

    public function testCheckReturnsTrueWhenNumberExists(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"existing_phone_numbers":["+7000"]}');
        $builder = MessageBuilder::new()
            ->setPhoneNumbers(['+7000'])
            ->setText('check');

        $result = $this->messages($http)->check($builder);
        self::assertTrue($result);
    }

    public function testCheckReturnsFalseWhenNoNumbers(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"existing_phone_numbers":[]}');
        $builder = MessageBuilder::new()->setText('check');

        $result = $this->messages($http)->check($builder);
        self::assertFalse($result);
    }

    public function testListExistReturnsNumbers(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"existing_phone_numbers":["+7000","+7001"]}');
        $builder = MessageBuilder::new()
            ->setPhoneNumbers(['+7000', '+7001'])
            ->setText('check');

        $result = $this->messages($http)->listExist($builder);
        self::assertSame(['+7000', '+7001'], $result);
    }

    public function testCheckWithResetSendsAccessToken(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"existing_phone_numbers":["+7000"]}');
        $builder = MessageBuilder::new()
            ->setReset(true)
            ->setBotToken('bot-tok')
            ->setPhoneNumbers(['+7000'])
            ->setText('check');

        $this->messages($http)->check($builder);
        self::assertStringContainsString('access_token=bot-tok', (string) $http->requests[0]->getUri());
        self::assertSame('', $http->requests[0]->getHeaderLine('Authorization'));
    }
}
