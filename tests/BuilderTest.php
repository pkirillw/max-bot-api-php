<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests;

use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Builder\Keyboard;
use Pkirillw\MaxBotApi\Builder\Message as MessageBuilder;
use Pkirillw\MaxBotApi\Scheme\Enum\Intent;

/**
 * Round-trip checks: builders → JSON → expected shape.
 */
final class BuilderTest extends TestCase
{
    public function testMessageBuilderProducesExpectedJson(): void
    {
        $message = MessageBuilder::new()
            ->setChat(123)
            ->setText('hi')
            ->setNotify(true);

        $json = json_encode($message->getBody(), JSON_UNESCAPED_UNICODE);
        self::assertJsonStringEqualsJsonString(
            '{"text":"hi","attachments":[],"notify":true}',
            $json,
        );
    }

    public function testKeyboardBuilderSerializesRowsAndButtons(): void
    {
        $keyboard = new Keyboard();
        $keyboard->addRow()
            ->addCallback('yes', 'y', Intent::Positive)
            ->addCallback('no', 'n', Intent::Negative);
        $keyboard->addRow()
            ->addLink('docs', 'https://dev.max.ru');

        $built = $keyboard->build();
        $json  = json_encode($built, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        self::assertStringContainsString('"text":"yes"', $json);
        self::assertStringContainsString('"intent":"positive"', $json);
        self::assertStringContainsString('"type":"link"', $json);
        self::assertStringContainsString('"url":"https://dev.max.ru"', $json);
    }
}
