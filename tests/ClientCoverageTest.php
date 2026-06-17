<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Client\Options;
use Pkirillw\MaxBotApi\Exception\ApiException;
use Pkirillw\MaxBotApi\Exception\NetworkException;
use Pkirillw\MaxBotApi\Exception\SerializationException;
use Pkirillw\MaxBotApi\Exception\TimeoutException;
use Pkirillw\MaxBotApi\Tests\Support\CaptureHttp;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;

final class ClientCoverageTest extends TestCase
{
    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    public function testGetOptionsReturnsConstructorOptions(): void
    {
        $opts = Options::default()->withBaseUrl('https://x');
        $client = new Client('tok', $opts, CaptureHttp::make($this->factory), $this->factory, $this->factory);
        self::assertSame('https://x', $client->getOptions()->baseUrl);
    }

    public function testSetOptionsReplacesOptions(): void
    {
        $client = new Client('tok', Options::default(), CaptureHttp::make($this->factory), $this->factory, $this->factory);
        $newOpts = Options::default()->withVersion('9.9');
        $client->setOptions($newOpts);
        self::assertSame('9.9', $client->getOptions()->version);
    }

    public function testResetOmitsAuthorizationHeader(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"ok":true}');
        $client = new Client('tok', Options::default(), $http, $this->factory, $this->factory);

        $client->requestJson('GET', 'me', [], null, reset: true);

        self::assertSame('', $http->lastRequest()->getHeaderLine('Authorization'));
    }

    public function testDecodeResponseReturnsEmptyArrayForEmptyBody(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '');
        $client = new Client('tok', Options::default(), $http, $this->factory, $this->factory);

        $result = $client->requestJson('GET', 'me');
        self::assertSame([], $result);
    }

    public function testDecodeResponseWrapsScalar(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '"hello"');
        $client = new Client('tok', Options::default(), $http, $this->factory, $this->factory);

        $result = $client->requestJson('GET', 'me');
        self::assertSame(['value' => 'hello'], $result);
    }

    public function testRaiseApiErrorWithNonJsonBody(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(500, 'Internal Server Error');
        $client = new Client('tok', Options::default(), $http, $this->factory, $this->factory);

        try {
            $client->requestJson('GET', 'me');
            self::fail('expected ApiException');
        } catch (ApiException $e) {
            self::assertSame(500, $e->httpCode);
            self::assertSame('', $e->apiCode);
            self::assertSame('Internal Server Error', $e->details);
        }
    }

    public function testRaiseApiErrorWithJsonBodyButNoCode(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(500, '{"foo":"bar"}');
        $client = new Client('tok', Options::default(), $http, $this->factory, $this->factory);

        try {
            $client->requestJson('GET', 'me');
            self::fail('expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('', $e->apiCode);
            self::assertNull($e->details);
        }
    }

    public function testRequestWithArrayBody(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"ok":true}');
        $client = new Client('tok', Options::default(), $http, $this->factory, $this->factory);

        $client->request('POST', 'me', [], ['custom' => 'body']);
        $req = $http->lastRequest();
        self::assertSame('application/json', $req->getHeaderLine('Content-Type'));
        self::assertJsonStringEqualsJsonString('{"custom":"body"}', (string) $req->getBody());
    }

    public function testSerializationExceptionOnUnencodableBody(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"ok":true}');
        $client = new Client('tok', Options::default(), $http, $this->factory, $this->factory);

        $binary = "\xB1\xB1"; // invalid UTF-8
        try {
            $client->request('POST', 'me', [], ['bad' => $binary]);
            self::fail('expected SerializationException');
        } catch (SerializationException $e) {
            self::assertStringContainsString('marshal', $e->getMessage());
        }
    }

    public function testSerializationExceptionOnUndecodableResponse(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, "\xB1\xB1");
        $client = new Client('tok', Options::default(), $http, $this->factory, $this->factory);

        try {
            $client->requestJson('GET', 'me');
            self::fail('expected SerializationException');
        } catch (SerializationException $e) {
            self::assertStringContainsString('unmarshal', $e->getMessage());
        }
    }

    public function testSendRawWithStringBody(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"ok":true}');
        $client = new Client('tok', Options::default(), $http, $this->factory, $this->factory);

        $response = $client->sendRaw('POST', 'https://example.com', 'raw-body', ['X-Custom' => 'foo']);
        self::assertSame(200, $response->getStatusCode());
        $req = $http->lastRequest();
        self::assertSame('foo', $req->getHeaderLine('X-Custom'));
        self::assertSame('raw-body', (string) $req->getBody());
    }

    public function testSendRawWithResourceBody(): void
    {
        $http = CaptureHttp::make($this->factory)->enqueue(200, '{"ok":true}');
        $client = new Client('tok', Options::default(), $http, $this->factory, $this->factory);

        $resource = fopen('php://memory', 'r+');
        self::assertIsResource($resource);
        fwrite($resource, 'from-resource');
        rewind($resource);

        $client->sendRaw('POST', 'https://example.com', $resource);
        self::assertSame('from-resource', (string) $http->lastRequest()->getBody());
    }

    public function testTimeoutExceptionOnNetworkException(): void
    {
        $psrHttp = $this->createMock(\Psr\Http\Client\ClientInterface::class);
        $psrHttp->method('sendRequest')->willThrowException(
            new class ('request', 'something') extends \RuntimeException implements NetworkExceptionInterface {
                public function __construct(private string $msg1, string $msg2)
                {
                    parent::__construct($msg1 . ' ' . $msg2);
                }
                public function getRequest(): RequestInterface
                {
                    throw new \LogicException('not implemented');
                }
            },
        );

        $client = new Client('tok', Options::default(), $psrHttp, $this->factory, $this->factory);

        $this->expectException(TimeoutException::class);
        $client->requestJson('GET', 'me');
    }

    public function testNetworkExceptionOnRequestException(): void
    {
        $psrHttp = $this->createMock(\Psr\Http\Client\ClientInterface::class);
        $psrHttp->method('sendRequest')->willThrowException(
            new class ('oops') extends \RuntimeException implements RequestExceptionInterface {
                public function __construct(string $msg)
                {
                    parent::__construct($msg);
                }
                public function getRequest(): RequestInterface
                {
                    throw new \LogicException('not implemented');
                }
            },
        );

        $client = new Client('tok', Options::default(), $psrHttp, $this->factory, $this->factory);

        $this->expectException(NetworkException::class);
        $client->requestJson('GET', 'me');
    }

    public function testTimeoutExceptionOnGenericClientTimeoutMessage(): void
    {
        $psrHttp = $this->createMock(\Psr\Http\Client\ClientInterface::class);
        $psrHttp->method('sendRequest')->willThrowException(
            new class ('operation timed out') extends \RuntimeException implements ClientExceptionInterface {},
        );

        $client = new Client('tok', Options::default(), $psrHttp, $this->factory, $this->factory);

        $this->expectException(TimeoutException::class);
        $client->requestJson('GET', 'me');
    }

    public function testNetworkExceptionOnGenericClientException(): void
    {
        $psrHttp = $this->createMock(\Psr\Http\Client\ClientInterface::class);
        $psrHttp->method('sendRequest')->willThrowException(
            new class ('some other failure') extends \RuntimeException implements ClientExceptionInterface {},
        );

        $client = new Client('tok', Options::default(), $psrHttp, $this->factory, $this->factory);

        $this->expectException(NetworkException::class);
        $client->requestJson('GET', 'me');
    }
}
