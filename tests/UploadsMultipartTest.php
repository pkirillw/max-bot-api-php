<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Client\Options;
use Pkirillw\MaxBotApi\Endpoint\Uploads;
use Pkirillw\MaxBotApi\Scheme\Enum\UploadType;
use Psr\Http\Client\ClientInterface as PsrHttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Asserts that the multipart body builder correctly quotes the filename and
 * that photo upload responses are parsed into PhotoTokens.
 */
final class UploadsMultipartTest extends TestCase
{
    public function testEscapesQuotesInFilename(): void
    {
        $factory = new Psr17Factory();
        $captured = null;
        $callCount = 0;

        $http = $this->createMock(PsrHttpClientInterface::class);
        $http->method('sendRequest')->willReturnCallback(
            static function (RequestInterface $request) use ($factory, &$captured, &$callCount): ResponseInterface {
                $callCount++;
                $path = $request->getUri()->getPath();
                if ($path === '/uploads' || str_ends_with($path, '/uploads')) {
                    $response = $factory->createResponse(200);
                    $response->getBody()->write('{"url":"https://upload.local/put","token":"upl-token"}');
                    return $response;
                }
                $captured = $request;
                $response = $factory->createResponse(200);
                $response->getBody()->write('');
                return $response;
            },
        );

        $client = new Client('tok', Options::default(), $http, $factory, $factory);
        $uploads = new Uploads($client, $http, $factory);

        $uploads->uploadMediaFromBytes(UploadType::Audio, 'data', 'weird"name.mp3');

        self::assertNotNull($captured);
        $body = (string) $captured->getBody();
        self::assertStringContainsString('filename="weird\"name.mp3"', $body);
        self::assertStringNotContainsString('filename="weird"name.mp3"', $body);
    }

    public function testParsesPhotoTokensFromUploadResponse(): void
    {
        $factory = new Psr17Factory();
        $http = $this->createMock(PsrHttpClientInterface::class);
        $http->method('sendRequest')->willReturnCallback(
            static function (RequestInterface $request) use ($factory): ResponseInterface {
                $path = $request->getUri()->getPath();
                if (str_ends_with($path, '/uploads')) {
                    $response = $factory->createResponse(200);
                    $response->getBody()->write('{"url":"https://upload.local/put","token":"upl-token"}');
                    return $response;
                }
                $response = $factory->createResponse(200);
                $response->getBody()->write('{"photos":{"s":{"token":"ph-s"},"m":{"token":"ph-m"},"l":{"token":"ph-l"}}}');
                return $response;
            },
        );

        $client = new Client('tok', Options::default(), $http, $factory, $factory);
        $uploads = new Uploads($client, $http, $factory);

        $tokens = $uploads->uploadPhotoFromBytes('image-data', 'pic.png');

        self::assertCount(3, $tokens->photos);
        self::assertSame('ph-s', $tokens->photos['s']->token);
        self::assertSame('ph-m', $tokens->photos['m']->token);
        self::assertSame('ph-l', $tokens->photos['l']->token);
    }
}
