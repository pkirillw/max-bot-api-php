<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests;

use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Client\Options;

final class OptionsTest extends TestCase
{
    public function testDefaults(): void
    {
        $opts = Options::default();
        self::assertSame(Options::DEFAULT_BASE_URL, $opts->baseUrl);
        self::assertSame(Options::DEFAULT_API_VERSION, $opts->version);
        self::assertFalse($opts->debug);
        self::assertNull($opts->debugChatId);
        self::assertFalse($opts->keepRawUpdates);
    }

    public function testWithBaseUrl(): void
    {
        $opts = Options::default()->withBaseUrl('https://other.example');
        self::assertSame('https://other.example', $opts->baseUrl);
        self::assertSame(Options::DEFAULT_API_VERSION, $opts->version);
        // original unchanged
        self::assertSame(Options::DEFAULT_BASE_URL, Options::default()->baseUrl);
    }

    public function testWithVersion(): void
    {
        $opts = Options::default()->withVersion('2.0');
        self::assertSame('2.0', $opts->version);
    }

    public function testWithDebug(): void
    {
        $opts = Options::default()->withDebug();
        self::assertTrue($opts->debug);
        $opts2 = Options::default()->withDebug(false);
        self::assertFalse($opts2->debug);
    }

    public function testWithDebugChatId(): void
    {
        $opts = Options::default()->withDebugChatId(42);
        self::assertSame(42, $opts->debugChatId);
    }

    public function testWithKeepRawUpdates(): void
    {
        $opts = Options::default()->withKeepRawUpdates();
        self::assertTrue($opts->keepRawUpdates);
        $opts2 = Options::default()->withKeepRawUpdates(false);
        self::assertFalse($opts2->keepRawUpdates);
    }

    public function testWithChainsCarryAllFields(): void
    {
        $opts = Options::default()
            ->withBaseUrl('https://x')
            ->withVersion('9')
            ->withDebug(true)
            ->withDebugChatId(7)
            ->withKeepRawUpdates(true);

        self::assertSame('https://x', $opts->baseUrl);
        self::assertSame('9', $opts->version);
        self::assertTrue($opts->debug);
        self::assertSame(7, $opts->debugChatId);
        self::assertTrue($opts->keepRawUpdates);
    }
}
