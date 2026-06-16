<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Scheme\Update\MessageCreatedUpdate;
use Pkirillw\MaxBotApi\Webhook\WebhookHandler;

/**
 * Behavior tests for the PSR-15 webhook handler.
 */
final class WebhookHandlerTest extends TestCase
{
    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    public function testRejectsMissingSecretHeader(): void
    {
        $handler = new WebhookHandler($this->factory, secret: 'top-secret');
        $request = $this->factory->createServerRequest('POST', '/wh')
            ->withHeader('Content-Type', 'application/json');
        $request->getBody()->write('{"update_type":"message_created","message":{"recipient":{"chat_id":1,"user_id":2},"body":{"mid":"m","seq":0,"text":"hi"},"sender":{"user_id":2}}}');

        $response = $handler->handle($request);

        self::assertSame(401, $response->getStatusCode());
    }

    public function testAcceptsValidSecret(): void
    {
        $handler = new WebhookHandler($this->factory, secret: 'top-secret');
        $handler->setHandler(function (object $update): void {
            self::assertInstanceOf(MessageCreatedUpdate::class, $update);
        });

        $request = $this->factory->createServerRequest('POST', '/wh')
            ->withHeader(Client::SECRET_HEADER, 'top-secret');
        $request->getBody()->write('{"update_type":"message_created","message":{"recipient":{"chat_id":1,"user_id":2},"body":{"mid":"m","seq":0,"text":"hi"},"sender":{"user_id":2}}}');

        $response = $handler->handle($request);
        self::assertSame(200, $response->getStatusCode());
    }

    public function testRejectsWrongSecret(): void
    {
        $handler = new WebhookHandler($this->factory, secret: 'top-secret');

        $request = $this->factory->createServerRequest('POST', '/wh')
            ->withHeader(Client::SECRET_HEADER, 'wrong-secret');
        $request->getBody()->write('{"update_type":"message_created"}');

        self::assertSame(401, $handler->handle($request)->getStatusCode());
    }

    public function testRejectsNonPost(): void
    {
        $handler = new WebhookHandler($this->factory, secret: '');

        $request = $this->factory->createServerRequest('GET', '/wh');
        self::assertSame(405, $handler->handle($request)->getStatusCode());
    }

    public function testRejectsEmptyBody(): void
    {
        $handler = new WebhookHandler($this->factory, secret: '');

        $request = $this->factory->createServerRequest('POST', '/wh');
        self::assertSame(400, $handler->handle($request)->getStatusCode());
    }

    public function testReturnsUnknownTypeAsBadRequest(): void
    {
        $handler = new WebhookHandler($this->factory, secret: '');

        $request = $this->factory->createServerRequest('POST', '/wh');
        $request->getBody()->write('{"update_type":"never_seen"}');

        self::assertSame(400, $handler->handle($request)->getStatusCode());
    }
}
