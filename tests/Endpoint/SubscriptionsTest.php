<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests\Endpoint;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Client\Options;
use Pkirillw\MaxBotApi\Endpoint\Subscriptions;
use Pkirillw\MaxBotApi\Scheme\Subscription;
use Pkirillw\MaxBotApi\Tests\Support\CaptureHttp;

final class SubscriptionsTest extends TestCase
{
    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    public function testGetSubscriptions(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, (string) json_encode([
            'subscriptions' => [
                ['url' => 'https://hook.example/a', 'time' => 100, 'update_types' => ['message_created']],
                ['url' => 'https://hook.example/b', 'time' => 200, 'version' => '1.2.5'],
            ],
        ], JSON_THROW_ON_ERROR));
        $subs = new Subscriptions(new Client('tok', Options::default(), $http, $this->factory, $this->factory));

        $result = $subs->getSubscriptions();

        self::assertSame('GET', $http->requests[0]->getMethod());
        self::assertSame('https://platform-api.max.ru/subscriptions?v=1.2.5', (string) $http->requests[0]->getUri());
        self::assertCount(2, $result->subscriptions);
        self::assertInstanceOf(Subscription::class, $result->subscriptions[0]);
        self::assertSame('https://hook.example/a', $result->subscriptions[0]->url);
        self::assertSame(100, $result->subscriptions[0]->time);
        self::assertSame(['message_created'], $result->subscriptions[0]->updateTypes);
        self::assertSame('1.2.5', $result->subscriptions[1]->version);
    }

    public function testSubscribe(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"success":true}');
        $subs = new Subscriptions(new Client('tok', Options::default(), $http, $this->factory, $this->factory));

        $result = $subs->subscribe(
            url: 'https://hook.example/x',
            updateTypes: ['message_created', 'message_callback'],
            secret: 'shh',
        );

        $req = $http->requests[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://platform-api.max.ru/subscriptions?v=1.2.5', (string) $req->getUri());
        self::assertJsonStringEqualsJsonString(
            '{"secret":"shh","url":"https://hook.example/x","update_types":["message_created","message_callback"],"version":"1.2.5"}',
            (string) $req->getBody(),
        );
        self::assertTrue($result->success);
    }

    public function testUnsubscribe(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"success":true,"message":"ok"}');
        $subs = new Subscriptions(new Client('tok', Options::default(), $http, $this->factory, $this->factory));

        $result = $subs->unsubscribe('https://hook.example/x');

        $req = $http->requests[0];
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame('https://platform-api.max.ru/subscriptions?url=' . rawurlencode('https://hook.example/x') . '&v=1.2.5', (string) $req->getUri());
        self::assertTrue($result->success);
        self::assertSame('ok', $result->message);
    }
}
