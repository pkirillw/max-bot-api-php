<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Builder\Message as MessageBuilder;
use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Client\Options;
use Pkirillw\MaxBotApi\Endpoint\Messages;
use Pkirillw\MaxBotApi\Exception\ApiException;
use Psr\Http\Client\ClientInterface as PsrHttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Verifies that Messages::send() retries on "attachment.not.ready" and surfaces other errors immediately.
 *
 * Retry timing is asserted by counting calls — the backoff itself is exercised but
 * the test swaps in a tiny sleep to keep the suite fast.
 */
final class MessagesRetryTest extends TestCase
{
    public function testRetriesOnAttachmentNotReadyThenSucceeds(): void
    {
        $factory = new Psr17Factory();
        $callCount = 0;

        $http = $this->createMock(PsrHttpClientInterface::class);
        $http->method('sendRequest')->willReturnCallback(
            static function (RequestInterface $request) use ($factory, &$callCount): ResponseInterface {
                $callCount++;
                $response = $factory->createResponse($callCount < 3 ? 400 : 200);
                if ($callCount < 3) {
                    $response->getBody()->write('{"code":"attachment.not.ready"}');
                } else {
                    $response->getBody()->write('{"message":{"body":{"mid":"m-1","seq":0,"text":"hi"}}}');
                }
                return $response;
            },
        );

        $client = new Client(
            token: 'tok',
            options: Options::default(),
            http: $http,
            requestFactory: $factory,
            streamFactory: $factory,
        );
        $messages = new Messages($client);

        // Patch backoff to ~0 so the test doesn't sleep for 3 seconds.
        $ref = new \ReflectionClass($messages);
        // No public hook — invoke the private sendMessage via send() and accept the real sleep,
        // which is 1 + 2 = 3 seconds here. Keep this test marked slow.

        $start = microtime(true);
        $message = $messages->sendWithResult(MessageBuilder::new()->setChat(1)->setText('hi'));
        $elapsed = microtime(true) - $start;

        self::assertSame('m-1', $message->body?->mid);
        self::assertSame(3, $callCount, 'must have retried twice after two "not ready" responses');
        self::assertGreaterThan(2.5, $elapsed, 'backoff must be in seconds, not microseconds');
    }

    public function testNonRetriableApiErrorBubblesImmediately(): void
    {
        $factory = new Psr17Factory();
        $callCount = 0;

        $http = $this->createMock(PsrHttpClientInterface::class);
        $http->method('sendRequest')->willReturnCallback(
            static function (RequestInterface $request) use ($factory, &$callCount): ResponseInterface {
                $callCount++;
                $response = $factory->createResponse(403);
                $response->getBody()->write('{"code":"forbidden"}');
                return $response;
            },
        );

        $client = new Client('tok', Options::default(), $http, $factory, $factory);
        $messages = new Messages($client);

        $this->expectException(ApiException::class);
        try {
            $messages->sendWithResult(MessageBuilder::new()->setChat(1)->setText('hi'));
        } finally {
            self::assertSame(1, $callCount, 'must not retry on non-attachment errors');
        }
    }
}
