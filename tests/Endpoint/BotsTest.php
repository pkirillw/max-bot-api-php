<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests\Endpoint;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Client\Options;
use Pkirillw\MaxBotApi\Endpoint\Bots;
use Pkirillw\MaxBotApi\Scheme\BotInfo;
use Pkirillw\MaxBotApi\Scheme\BotPatch;
use Pkirillw\MaxBotApi\Tests\Support\CaptureHttp;

final class BotsTest extends TestCase
{
    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    public function testGetBot(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"name":"my-bot","user_id":42,"username":"mybot"}');
        $bots = new Bots(new Client('tok', Options::default(), $http, $this->factory, $this->factory));

        $info = $bots->getBot();

        self::assertSame('GET', $http->requests[0]->getMethod());
        self::assertSame('https://platform-api.max.ru/me?v=1.2.5', (string) $http->requests[0]->getUri());
        self::assertInstanceOf(BotInfo::class, $info);
        self::assertSame('my-bot', $info->name);
        self::assertSame(42, $info->userId);
    }

    public function testPatchBot(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"name":"new","user_id":1,"username":"x"}');
        $bots = new Bots(new Client('tok', Options::default(), $http, $this->factory, $this->factory));

        $patch = new BotPatch(name: 'new', description: 'desc');
        $info = $bots->patchBot($patch);

        $req = $http->requests[0];
        self::assertSame('PATCH', $req->getMethod());
        self::assertSame('https://platform-api.max.ru/me?v=1.2.5', (string) $req->getUri());
        self::assertJsonStringEqualsJsonString(
            '{"name":"new","description":"desc"}',
            (string) $req->getBody(),
        );
        self::assertSame('new', $info->name);
    }
}
