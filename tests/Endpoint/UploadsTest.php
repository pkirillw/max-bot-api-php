<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests\Endpoint;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Client\Options;
use Pkirillw\MaxBotApi\Endpoint\Uploads;
use Pkirillw\MaxBotApi\Exception\ApiException;
use Pkirillw\MaxBotApi\Exception\MaxBotApiException;
use Pkirillw\MaxBotApi\Exception\NetworkException;
use Pkirillw\MaxBotApi\Scheme\Enum\UploadType;
use Pkirillw\MaxBotApi\Tests\Support\CaptureHttp;
use Psr\Http\Client\ClientInterface as PsrHttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class UploadsTest extends TestCase
{
    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    private function uploads(CaptureHttp $http, ?PsrHttpClientInterface $fetcher = null): Uploads
    {
        $client = new Client('tok', Options::default(), $http, $this->factory, $this->factory);
        return new Uploads($client, $fetcher ?? $http, $this->factory);
    }

    private function enqueueUploadResponse(CaptureHttp $http, string $url, string $token, string $secondBody = '', int $secondStatus = 200): void
    {
        $http->enqueue(200, '{"url":"' . $url . '","token":"' . $token . '"}');
        $http->enqueue($secondStatus, $secondBody);
    }

    public function testUploadMediaFromBytesAudioReturnsTokenFromUploadEndpoint(): void
    {
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'audio-tok');

        $info = $this->uploads($http)->uploadMediaFromBytes(UploadType::Audio, 'audio-data', 'song.mp3');

        self::assertSame('audio-tok', $info->token);
        self::assertSame(0, $info->fileId);
    }

    public function testUploadMediaFromBytesVideo(): void
    {
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'vid-tok');

        $info = $this->uploads($http)->uploadMediaFromBytes(UploadType::Video, 'video-data', 'v.mp4');
        self::assertSame('vid-tok', $info->token);
    }

    public function testUploadMediaFromBytesFile(): void
    {
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'file-tok');

        $info = $this->uploads($http)->uploadMediaFromBytes(UploadType::File, 'file-data', 'doc.pdf');
        self::assertSame('file-tok', $info->token);
    }

    public function testUploadMediaFromBytesWithNoFilename(): void
    {
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'no-name');

        $this->uploads($http)->uploadMediaFromBytes(UploadType::Audio, 'data');
        $body = (string) $http->requests[1]->getBody();
        self::assertStringContainsString('filename="file"', $body);
    }

    public function testUploadMediaFromBase64(): void
    {
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'b64-tok');

        $info = $this->uploads($http)->uploadMediaFromBase64(UploadType::Audio, base64_encode('decoded'), 'x.mp3');
        self::assertSame('b64-tok', $info->token);
    }

    public function testUploadMediaFromBase64ThrowsOnInvalidInput(): void
    {
        $this->expectException(MaxBotApiException::class);
        $this->expectExceptionMessage('invalid base64');
        $this->uploads(CaptureHttp::make($this->factory))->uploadMediaFromBase64(UploadType::Audio, '%%%not-base64%%%');
    }

    public function testUploadMediaFromFileReadsDisk(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'upl-test');
        file_put_contents($tmp, 'from-disk');
        try {
            $http = CaptureHttp::make($this->factory);
            $this->enqueueUploadResponse($http, 'https://up.local/put', 'disk-tok');

            $info = $this->uploads($http)->uploadMediaFromFile(UploadType::File, $tmp);
            self::assertSame('disk-tok', $info->token);
        } finally {
            unlink($tmp);
        }
    }

    public function testUploadMediaFromFileThrowsWhenUnreadable(): void
    {
        $this->expectException(MaxBotApiException::class);
        $this->expectExceptionMessage('failed to open file');
        $this->uploads(CaptureHttp::make($this->factory))->uploadMediaFromFile(UploadType::File, '/no/such/file');
    }

    public function testUploadPhotoFromBytesWithPhotosKey(): void
    {
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'photo-tok', '{"photos":{"s":{"token":"ph-s"}}}');

        $tokens = $this->uploads($http)->uploadPhotoFromBytes('image-data', 'pic.png');
        self::assertSame('ph-s', $tokens->photos['s']->token);
    }

    public function testUploadPhotoFromBytesWithEmptyBodyReturnsEmptyTokens(): void
    {
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'photo-tok');

        $tokens = $this->uploads($http)->uploadPhotoFromBytes('data', 'pic.png');
        self::assertSame([], $tokens->photos);
    }

    public function testUploadPhotoFromBytesWithNonArrayJsonReturnsEmptyTokens(): void
    {
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'photo-tok', '"not an object"');

        $tokens = $this->uploads($http)->uploadPhotoFromBytes('data', 'pic.png');
        self::assertSame([], $tokens->photos);
    }

    public function testUploadPhotoFromBase64(): void
    {
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'p64', '{"photos":{"s":{"token":"t"}}}');

        $tokens = $this->uploads($http)->uploadPhotoFromBase64(base64_encode('img'), 'pic.png');
        self::assertSame('t', $tokens->photos['s']->token);
    }

    public function testUploadPhotoFromBase64InvalidThrows(): void
    {
        $this->expectException(MaxBotApiException::class);
        $this->uploads(CaptureHttp::make($this->factory))->uploadPhotoFromBase64('not-base64!!!');
    }

    public function testUploadPhotoFromFileReadsDisk(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'img-test');
        file_put_contents($tmp, 'img-data');
        try {
            $http = CaptureHttp::make($this->factory);
            $this->enqueueUploadResponse($http, 'https://up.local/put', 'p', '{"photos":{"s":{"token":"disk-tok"}}}');

            $tokens = $this->uploads($http)->uploadPhotoFromFile($tmp);
            self::assertSame('disk-tok', $tokens->photos['s']->token);
        } finally {
            unlink($tmp);
        }
    }

    public function testUploadPhotoFromFileThrowsOnMissingFile(): void
    {
        $this->expectException(MaxBotApiException::class);
        $this->uploads(CaptureHttp::make($this->factory))->uploadPhotoFromFile('/no/such/image.png');
    }

    public function testUploadMediaFromUrlFetchesAndUploads(): void
    {
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'url-tok');

        // Use separate fetcher mock so the upload client doesn't intercept the GET
        $fetcher = $this->createMock(PsrHttpClientInterface::class);
        $fetcher->method('sendRequest')->willReturnCallback(
            function (RequestInterface $request): ResponseInterface {
                $resp = $this->factory->createResponse(200);
                $resp->getBody()->write('image-bytes');
                return $resp;
            },
        );

        $info = $this->uploads($http, $fetcher)->uploadMediaFromUrl(UploadType::File, 'https://example.com/path/doc.pdf');
        self::assertSame('url-tok', $info->token);
        // Verify multipart body uses the URL basename
        $body = (string) $http->requests[1]->getBody();
        self::assertStringContainsString('filename="doc.pdf"', $body);
    }

    public function testUploadMediaFromUrlHandlesNetworkError(): void
    {
        $fetcher = $this->createMock(PsrHttpClientInterface::class);
        $fetcher->method('sendRequest')->willThrowException(
            new class ('dns fail') extends \RuntimeException implements \Psr\Http\Client\ClientExceptionInterface {},
        );

        $this->expectException(NetworkException::class);
        $this->uploads(CaptureHttp::make($this->factory), $fetcher)->uploadMediaFromUrl(UploadType::File, 'https://example.com/x.txt');
    }

    public function testUploadMediaFromUrlFailsOnNon2xx(): void
    {
        $fetcher = $this->createMock(PsrHttpClientInterface::class);
        $fetcher->method('sendRequest')->willReturnCallback(
            function (RequestInterface $request): ResponseInterface {
                return $this->factory->createResponse(404);
            },
        );

        $this->expectException(MaxBotApiException::class);
        $this->expectExceptionMessage('failed to fetch URL');
        $this->uploads(CaptureHttp::make($this->factory), $fetcher)->uploadMediaFromUrl(UploadType::File, 'https://example.com/x.txt');
    }

    public function testUploadPhotoFromUrl(): void
    {
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'ph-url', '{"photos":{"s":{"token":"u-s"}}}');

        $fetcher = $this->createMock(PsrHttpClientInterface::class);
        $fetcher->method('sendRequest')->willReturnCallback(
            function (RequestInterface $request): ResponseInterface {
                $resp = $this->factory->createResponse(200);
                $resp->getBody()->write('img');
                return $resp;
            },
        );

        $tokens = $this->uploads($http, $fetcher)->uploadPhotoFromUrl('https://example.com/p.png');
        self::assertSame('u-s', $tokens->photos['s']->token);
    }

    public function testUploadThrowsApiErrorOnNon2xxUploadResponse(): void
    {
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'tok', '{"code":"bad.upload","message":"oops"}', 422);

        $this->expectException(ApiException::class);
        try {
            $this->uploads($http)->uploadMediaFromBytes(UploadType::Audio, 'x', 'x.mp3');
        } catch (ApiException $e) {
            self::assertSame(422, $e->httpCode);
            self::assertSame('bad.upload', $e->apiCode);
            self::assertSame('oops', $e->details);
            throw $e;
        }
    }

    public function testUploadThrowsApiErrorWithPlainTextBody(): void
    {
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'tok', 'Internal Error', 500);

        try {
            $this->uploads($http)->uploadMediaFromBytes(UploadType::Audio, 'x', 'x.mp3');
            self::fail('expected ApiException');
        } catch (ApiException $e) {
            self::assertSame(500, $e->httpCode);
            self::assertSame('Internal Error', $e->details);
        }
    }

    public function testUploadBytesPhotoWithEmptyResponseReturnsFallbackToken(): void
    {
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'fallback-tok');

        $info = $this->uploads($http)->uploadMediaFromBytes(UploadType::Photo, 'data', 'p.png');
        self::assertSame('fallback-tok', $info->token);
    }

    public function testUploadBytesPhotoWithPhotosReturnsFirstToken(): void
    {
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'fallback', '{"photos":{"s":{"token":"from-photos"}}}');

        $info = $this->uploads($http)->uploadMediaFromBytes(UploadType::Photo, 'data', 'p.png');
        self::assertSame('from-photos', $info->token);
    }

    public function testFilenameFromUrlHandlesPathWithoutBasename(): void
    {
        // Empty filename path - we can't easily test private filenameFromUrl
        // but the uploadMediaFromUrl code path covers it.
        $http = CaptureHttp::make($this->factory);
        $this->enqueueUploadResponse($http, 'https://up.local/put', 'no-fn');

        $fetcher = $this->createMock(PsrHttpClientInterface::class);
        $fetcher->method('sendRequest')->willReturnCallback(
            function (RequestInterface $request): ResponseInterface {
                $resp = $this->factory->createResponse(200);
                $resp->getBody()->write('img');
                return $resp;
            },
        );

        $info = $this->uploads($http, $fetcher)->uploadMediaFromUrl(UploadType::Audio, 'https://example.com/');
        self::assertSame('no-fn', $info->token);
        $body = (string) $http->requests[1]->getBody();
        self::assertStringContainsString('filename="file"', $body);
    }
}
