<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests\Support;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface as PsrHttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Test helper: PSR-18 client that records every outgoing request and returns
 * preset responses keyed by call index.
 *
 * Usage:
 *   $http = CaptureHttp::make($factory)
 *       ->enqueue(200, '{"ok":true}')
 *       ->enqueue(404, '{"code":"not.found"}');
 *   $http->run(function (RequestInterface $r) use ($client): ResponseInterface {
 *       return $client->sendRequest($r);
 *   });
 *   $captured = $http->requests[0];
 */
final class CaptureHttp implements PsrHttpClientInterface
{
    /** @var list<ResponseInterface> */
    public array $queue = [];
    /** @var list<RequestInterface> */
    public array $requests = [];
    public int $calls = 0;

    private function __construct(private Psr17Factory $factory) {}

    public static function make(?Psr17Factory $factory = null): self
    {
        return new self($factory ?? new Psr17Factory());
    }

    public function enqueue(int $status = 200, string $body = ''): self
    {
        $response = $this->factory->createResponse($status);
        if ($body !== '') {
            $response->getBody()->write($body);
        }
        $this->queue[] = $response;
        return $this;
    }

    public function enqueueCallback(\Closure $fn): self
    {
        $this->queue[] = $fn($this->factory);
        return $this;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        $this->calls++;
        return array_shift($this->queue) ?? $this->factory->createResponse(200);
    }

    public function expectCallCount(int $n): void
    {
        TestCase::assertSame($n, $this->calls, 'unexpected number of HTTP calls');
    }

    public function lastRequest(): RequestInterface
    {
        TestCase::assertNotEmpty($this->requests, 'no HTTP calls captured');
        return $this->requests[array_key_last($this->requests)];
    }
}
