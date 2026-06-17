<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests\Scheme;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Scheme\Attachment\AudioAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoAttachmentRequestPayload;
use Pkirillw\MaxBotApi\Scheme\Attachment\UploadedInfo;
use Pkirillw\MaxBotApi\Scheme\BotPatch;
use Pkirillw\MaxBotApi\Scheme\Button\Button;
use Pkirillw\MaxBotApi\Scheme\Enum\ButtonType;
use Pkirillw\MaxBotApi\Webhook\WebhookHandler;

final class RemainingCoverageTest extends TestCase
{
    public function testPhotoAttachmentRequestPayloadFromJsonWithPhotos(): void
    {
        $payload = PhotoAttachmentRequestPayload::fromJson([
            'url' => 'https://img',
            'token' => 'tok',
            'photos' => [
                's' => ['token' => 'ph-s'],
                'm' => ['token' => 'ph-m'],
            ],
        ]);
        self::assertSame('https://img', $payload->url);
        self::assertSame('tok', $payload->token);
        self::assertCount(2, $payload->photos);
        self::assertSame('ph-s', $payload->photos['s']->token);
        self::assertSame('ph-m', $payload->photos['m']->token);
    }

    public function testAudioAttachmentRequestJsonSerialize(): void
    {
        $req = new AudioAttachmentRequest(new UploadedInfo(fileId: 9, token: 'aud-tok'));
        self::assertSame([
            'type' => 'audio', 'payload' => ['file_id' => 9, 'token' => 'aud-tok'],
        ], $req->jsonSerialize());
    }

    public function testBotPatchWithPhotoSerializes(): void
    {
        $patch = new BotPatch(photo: new PhotoAttachmentRequestPayload(token: 'ph'));
        $encoded = $patch->jsonSerialize();
        self::assertSame(['token' => 'ph'], $encoded['photo']);
    }

    public function testButtonGenericJsonSerialize(): void
    {
        $b = new Button(type: ButtonType::Callback, text: 'X');
        self::assertSame('X', $b->getText());
        self::assertSame(['type' => 'callback', 'text' => 'X'], $b->jsonSerialize());
    }

    public function testWebhookHandlerSetHandlerReturnsSelf(): void
    {
        $factory = new Psr17Factory();
        $handler = new WebhookHandler($factory);
        $callable = static function (): void {};
        $returned = $handler->setHandler($callable);
        self::assertSame($handler, $returned);
    }
}
