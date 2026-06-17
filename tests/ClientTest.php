<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Client\Options;
use Pkirillw\MaxBotApi\Exception\EmptyTokenException;
use Psr\Http\Client\ClientInterface as PsrHttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Transport-level coverage: empty-token guard and URI/header assembly.
 */
final class ClientTest extends TestCase
{
    public function testEmptyTokenThrowsEmptyTokenException(): void
    {
        $this->expectException(EmptyTokenException::class);

        new Client(
            token: '',
            options: Options::default(),
            http: $this->createMock(PsrHttpClientInterface::class),
            requestFactory: new Psr17Factory(),
            streamFactory: new Psr17Factory(),
        );
    }

    public function testRequestInjectsAuthorizationAndUserAgentAndVersion(): void
    {
        $factory = new Psr17Factory();
        $captured = null;

        $http = $this->createMock(PsrHttpClientInterface::class);
        $http->method('sendRequest')->willReturnCallback(
            static function (RequestInterface $request) use (&$captured, $factory): ResponseInterface {
                $captured = $request;
                $response = $factory->createResponse(200);
                $response->getBody()->write('{"ok":true}');
                return $response;
            },
        );

        $client = new Client(
            token: 'tok-1',
            options: Options::default()->withVersion('9.9'),
            http: $http,
            requestFactory: $factory,
            streamFactory: $factory,
        );

        $client->requestJson('GET', 'me');

        self::assertNotNull($captured);
        self::assertSame('tok-1', $captured->getHeaderLine('Authorization'));
        self::assertStringContainsString('max-bot-api-client-php/', $captured->getHeaderLine('User-Agent'));
        self::assertStringContainsString('v=9.9', $captured->getUri()->getQuery());
        self::assertSame('https://platform-api.max.ru/me?v=9.9', (string) $captured->getUri());
    }

    public function testRequestJsonThrowsApiErrorOnNon2xx(): void
    {
        $factory = new Psr17Factory();

        $http = $this->createMock(PsrHttpClientInterface::class);
        $http->method('sendRequest')->willReturnCallback(
            static function (RequestInterface $request) use ($factory): ResponseInterface {
                $response = $factory->createResponse(400);
                $response->getBody()->write('{"code":"bad.request","message":"oops"}');
                return $response;
            },
        );

        $client = new Client(
            token: 'tok-1',
            options: Options::default(),
            http: $http,
            requestFactory: $factory,
            streamFactory: $factory,
        );

        try {
            $client->requestJson('GET', 'me');
            self::fail('expected ApiException');
        } catch (\Pkirillw\MaxBotApi\Exception\ApiException $e) {
            self::assertSame(400, $e->httpCode);
            self::assertSame('bad.request', $e->apiCode);
            self::assertSame('oops', $e->details);
        }
    }
}
