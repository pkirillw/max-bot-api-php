<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Scheme\Update\MessageCreatedUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\UpdateInterface;
use Pkirillw\MaxBotApi\Webhook\WebhookHandler;
use Pkirillw\MaxBotApi\Webhook\WebhookMiddleware;
use Psr\Http\Server\RequestHandlerInterface;

final class WebhookMiddlewareTest extends TestCase
{
    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    public function testMiddlewareProcessesPostAndInvokesHandler(): void
    {
        $received = null;
        $middleware = new WebhookMiddleware(
            $this->factory,
            secret: '',
            updateHandler: static function (UpdateInterface $update) use (&$received): void {
                $received = $update;
            },
        );

        $request = $this->factory->createServerRequest('POST', '/wh');
        $request->getBody()->write('{"update_type":"message_created","message":{"recipient":{"chat_id":1,"user_id":2},"body":{"mid":"m","seq":0,"text":"hi"},"sender":{"user_id":2}}}');

        $fallback = $this->createMock(RequestHandlerInterface::class);
        $fallback->expects(self::never())->method('handle');

        $response = $middleware->process($request, $fallback);
        self::assertSame(200, $response->getStatusCode());
        self::assertInstanceOf(MessageCreatedUpdate::class, $received);
    }

    public function testMiddlewarePassesThroughNonPost(): void
    {
        $middleware = new WebhookMiddleware($this->factory);

        $request = $this->factory->createServerRequest('GET', '/wh');
        $fallback = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->factory->createResponse(204);
        $fallback->expects(self::once())->method('handle')->willReturn($expectedResponse);

        $response = $middleware->process($request, $fallback);
        self::assertSame($expectedResponse, $response);
    }

    public function testMiddlewareWithHandlerReplacesCallable(): void
    {
        $first = false;
        $second = false;
        $middleware = new WebhookMiddleware(
            $this->factory,
            secret: '',
            updateHandler: static function () use (&$first): void {
                $first = true;
            },
        );

        $middleware->withHandler(static function () use (&$second): void {
            $second = true;
        });

        $request = $this->factory->createServerRequest('POST', '/wh');
        $request->getBody()->write('{"update_type":"message_created","message":{"recipient":{"chat_id":1,"user_id":2},"body":{"mid":"m","seq":0,"text":"hi"},"sender":{"user_id":2}}}');

        $fallback = $this->createMock(RequestHandlerInterface::class);
        $middleware->process($request, $fallback);

        self::assertFalse($first);
        self::assertTrue($second);
    }

    public function testMiddlewareValidatesSecret(): void
    {
        $middleware = new WebhookMiddleware($this->factory, secret: 'top-secret');

        $request = $this->factory->createServerRequest('POST', '/wh')
            ->withHeader('X-Max-Bot-Api-Secret', 'wrong');

        $fallback = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $fallback);
        self::assertSame(401, $response->getStatusCode());
    }

    public function testWebhookHandlerWithoutCallableStillAcceptsValidUpdate(): void
    {
        $handler = new WebhookHandler($this->factory, secret: '');

        $request = $this->factory->createServerRequest('POST', '/wh');
        $request->getBody()->write('{"update_type":"message_created","message":{"recipient":{"chat_id":1,"user_id":2},"body":{"mid":"m","seq":0,"text":"hi"},"sender":{"user_id":2}}}');

        $response = $handler->handle($request);
        self::assertSame(200, $response->getStatusCode());
    }

    public function testWebhookHandlerReturnsJsonContentType(): void
    {
        $handler = new WebhookHandler($this->factory, secret: '');

        $request = $this->factory->createServerRequest('GET', '/wh');
        $response = $handler->handle($request);

        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        self::assertSame('{"error":"method not allowed"}', (string) $response->getBody());
    }
}
