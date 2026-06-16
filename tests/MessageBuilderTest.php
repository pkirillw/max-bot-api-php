<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests;

use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Builder\Message as MessageBuilder;
use Pkirillw\MaxBotApi\Scheme\Enum\Format;

/**
 * Coverage for outgoing-message builder: setters round-trip into the serialized body.
 */
final class MessageBuilderTest extends TestCase
{
    public function testBotTokenPropagatesToBody(): void
    {
        $body = MessageBuilder::new()
            ->setChat(123)
            ->setText('hi')
            ->setBotToken('token-abc')
            ->getBody();

        $json = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        self::assertStringContainsString('"bot_token":"token-abc"', $json);
    }

    public function testFormatEmittedOnlyWhenSet(): void
    {
        $withoutFormat = MessageBuilder::new()->setChat(1)->setText('x')->getBody();
        self::assertArrayNotHasKey('format', $withoutFormat->jsonSerialize());

        $withFormat = MessageBuilder::new()->setChat(1)->setText('x')->setFormat(Format::Markdown)->getBody();
        self::assertSame('markdown', $withFormat->jsonSerialize()['format']);
    }

    public function testForwardClearsText(): void
    {
        $builder = MessageBuilder::new()->setText('original')->setChat(1);
        $builder->setForward('mid.123');

        $body = $builder->getBody()->jsonSerialize();
        self::assertSame('', $body['text']);
        self::assertSame('forward', $body['link']['type']);
        self::assertSame('mid.123', $body['link']['mid']);
    }
}
