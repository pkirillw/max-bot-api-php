<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests\Endpoint;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Client\Options;
use Pkirillw\MaxBotApi\Endpoint\Debugs;
use Pkirillw\MaxBotApi\Scheme\Message;
use Pkirillw\MaxBotApi\Scheme\Update\MessageCreatedUpdate;
use Pkirillw\MaxBotApi\Tests\Support\CaptureHttp;

final class DebugsTest extends TestCase
{
    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    public function testDisabledByDefault(): void
    {
        $http = CaptureHttp::make($this->factory);
        $debugs = new Debugs(new Client('tok', Options::default(), $http, $this->factory, $this->factory));

        self::assertNull($debugs->getChatId());

        $debugs->sendText('hello');
        $debugs->sendErr(new \RuntimeException('boom'));

        $http->expectCallCount(0);
    }

    public function testWithChatIdEnablesAndPersists(): void
    {
        $debugs = new Debugs(new Client('tok', Options::default(), CaptureHttp::make($this->factory), $this->factory, $this->factory));

        $returned = $debugs->withChatId(42);
        self::assertSame($debugs, $returned);
        self::assertSame(42, $debugs->getChatId());
    }

    public function testConstructorChatId(): void
    {
        $debugs = new Debugs(new Client('tok', Options::default(), CaptureHttp::make($this->factory), $this->factory, $this->factory), 99);
        self::assertSame(99, $debugs->getChatId());
    }

    public function testSendTextPostsMessage(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"success":true}');
        $debugs = new Debugs(new Client('tok', Options::default(), $http, $this->factory, $this->factory), 7);

        $debugs->sendText('hello world');

        $req = $http->requests[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://platform-api.max.ru/messages?chat_id=7&v=1.2.5', (string) $req->getUri());
        self::assertJsonStringEqualsJsonString('{"text":"hello world","attachments":[],"notify":false}', (string) $req->getBody());
    }

    public function testSendErrPostsMessageFromThrowable(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"success":true}');
        $debugs = new Debugs(new Client('tok', Options::default(), $http, $this->factory, $this->factory), 7);

        $debugs->sendErr(new \LogicException('bang'));

        $req = $http->requests[0];
        self::assertSame('POST', $req->getMethod());
        self::assertJsonStringEqualsJsonString('{"text":"bang","attachments":[],"notify":false}', (string) $req->getBody());
    }

    public function testSendUpdateDelegatesToDebugRaw(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"success":true}');
        $debugs = new Debugs(new Client('tok', Options::default(), $http, $this->factory, $this->factory), 7);

        $update = new MessageCreatedUpdate(
            message: new Message(),
            timestamp: 1000,
            debugRaw: 'raw-debug-text',
        );

        $debugs->send($update);

        $req = $http->requests[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://platform-api.max.ru/messages?chat_id=7&v=1.2.5', (string) $req->getUri());
    }
}
