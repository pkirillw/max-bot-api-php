<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests;

use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Exception\ApiException;
use Pkirillw\MaxBotApi\Exception\EmptyTokenException;
use Pkirillw\MaxBotApi\Exception\MaxBotApiException;
use Pkirillw\MaxBotApi\Exception\NetworkException;
use Pkirillw\MaxBotApi\Exception\SerializationException;
use Pkirillw\MaxBotApi\Exception\TimeoutException;
use Pkirillw\MaxBotApi\Exception\UpdateParsingException;

final class ExceptionCoverageTest extends TestCase
{
    public function testApiExceptionMessages(): void
    {
        $noCode = new ApiException(httpCode: 500, apiCode: '');
        self::assertSame('API error 500', $noCode->getMessage());

        $withCode = new ApiException(httpCode: 400, apiCode: 'bad.request');
        self::assertSame('API error 400: bad.request', $withCode->getMessage());

        $withCodeAndDetails = new ApiException(httpCode: 400, apiCode: 'bad.request', details: 'oops');
        self::assertSame('API error 400: bad.request (oops)', $withCodeAndDetails->getMessage());

        $detailsOnly = new ApiException(httpCode: 500, apiCode: '', details: 'internal');
        self::assertSame('API error 500 (internal)', $detailsOnly->getMessage());
    }

    public function testIsAttachmentNotReadyTrue(): void
    {
        $e = new ApiException(httpCode: 400, apiCode: 'attachment.not.ready');
        self::assertTrue($e->isAttachmentNotReady());
    }

    public function testIsAttachmentNotReadyFalse(): void
    {
        $e = new ApiException(httpCode: 400, apiCode: 'something.else');
        self::assertFalse($e->isAttachmentNotReady());
    }

    public function testEmptyTokenExceptionMessage(): void
    {
        $e = new EmptyTokenException();
        self::assertSame('bot token is empty', $e->getMessage());
        self::assertInstanceOf(MaxBotApiException::class, $e);
    }

    public function testNetworkExceptionWrapsPrevious(): void
    {
        $prev = new \RuntimeException('boom');
        $e = new NetworkException('POST me', $prev);
        self::assertSame('POST me', $e->operation);
        self::assertStringContainsString('POST me', $e->getMessage());
        self::assertStringContainsString('boom', $e->getMessage());
        self::assertSame($prev, $e->getPrevious());
    }

    public function testSerializationExceptionMessage(): void
    {
        $prev = new \JsonException('bad json');
        $e = new SerializationException('marshal', 'request body', $prev);
        self::assertStringContainsString('marshal', $e->getMessage());
        self::assertStringContainsString('request body', $e->getMessage());
        self::assertStringContainsString('bad json', $e->getMessage());
    }

    public function testTimeoutExceptionMessages(): void
    {
        $withReason = new TimeoutException('GET me', 'deadline exceeded');
        self::assertSame('GET me', $withReason->operation);
        self::assertStringContainsString('deadline exceeded', $withReason->getMessage());

        $withoutReason = new TimeoutException('GET me');
        self::assertStringContainsString('timeout error during GET me', $withoutReason->getMessage());
    }

    public function testUpdateParsingException(): void
    {
        $prev = new \RuntimeException('inner');
        $e = new UpdateParsingException('bad update', 0, $prev);
        self::assertSame('bad update', $e->getMessage());
        self::assertSame($prev, $e->getPrevious());
        self::assertInstanceOf(MaxBotApiException::class, $e);
    }
}
