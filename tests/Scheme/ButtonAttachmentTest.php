<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests\Scheme;

use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Scheme\Attachment\AttachmentParser;
use Pkirillw\MaxBotApi\Scheme\Attachment\AttachmentPayload;
use Pkirillw\MaxBotApi\Scheme\Attachment\AudioAttachment;
use Pkirillw\MaxBotApi\Scheme\Attachment\AudioAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\ContactAttachment;
use Pkirillw\MaxBotApi\Scheme\Attachment\ContactAttachmentPayload;
use Pkirillw\MaxBotApi\Scheme\Attachment\ContactAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\ContactAttachmentRequestPayload;
use Pkirillw\MaxBotApi\Scheme\Attachment\FileAttachment;
use Pkirillw\MaxBotApi\Scheme\Attachment\FileAttachmentPayload;
use Pkirillw\MaxBotApi\Scheme\Attachment\FileAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\InlineKeyboardAttachment;
use Pkirillw\MaxBotApi\Scheme\Attachment\InlineKeyboardAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\LocationAttachment;
use Pkirillw\MaxBotApi\Scheme\Attachment\LocationAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\MediaAttachmentPayload;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoAttachment;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoAttachmentPayload;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoAttachmentRequestPayload;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoToken;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoTokens;
use Pkirillw\MaxBotApi\Scheme\Attachment\ShareAttachment;
use Pkirillw\MaxBotApi\Scheme\Attachment\StickerAttachment;
use Pkirillw\MaxBotApi\Scheme\Attachment\StickerAttachmentPayload;
use Pkirillw\MaxBotApi\Scheme\Attachment\StickerAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\StickerAttachmentRequestPayload;
use Pkirillw\MaxBotApi\Scheme\Attachment\UploadedInfo;
use Pkirillw\MaxBotApi\Scheme\Attachment\VideoAttachment;
use Pkirillw\MaxBotApi\Scheme\Button\Button;
use Pkirillw\MaxBotApi\Scheme\Button\ButtonParser;
use Pkirillw\MaxBotApi\Scheme\Button\CallbackButton;
use Pkirillw\MaxBotApi\Scheme\Button\ClipboardButton;
use Pkirillw\MaxBotApi\Scheme\Button\LinkButton;
use Pkirillw\MaxBotApi\Scheme\Button\MessageButton;
use Pkirillw\MaxBotApi\Scheme\Button\OpenAppButton;
use Pkirillw\MaxBotApi\Scheme\Button\RequestContactButton;
use Pkirillw\MaxBotApi\Scheme\Button\RequestGeoLocationButton;
use Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType;
use Pkirillw\MaxBotApi\Scheme\Enum\ButtonType;
use Pkirillw\MaxBotApi\Scheme\Enum\Intent;
use Pkirillw\MaxBotApi\Scheme\Keyboard;

final class ButtonAttachmentTest extends TestCase
{
    public function testLinkButtonRoundTrip(): void
    {
        $b = LinkButton::fromJson(['type' => 'link', 'text' => 'Go', 'url' => 'https://x']);
        self::assertSame(ButtonType::Link, $b->getType());
        self::assertSame('Go', $b->getText());
        self::assertSame([
            'type' => 'link', 'text' => 'Go', 'url' => 'https://x',
        ], $b->jsonSerialize());
    }

    public function testCallbackButtonRoundTripWithIntent(): void
    {
        $b = CallbackButton::fromJson(['text' => 'Ok', 'payload' => 'p', 'intent' => 'positive']);
        self::assertSame(ButtonType::Callback, $b->getType());
        self::assertSame(Intent::Positive, $b->intent);
        self::assertSame([
            'type' => 'callback', 'text' => 'Ok', 'payload' => 'p', 'intent' => 'positive',
        ], $b->jsonSerialize());
    }

    public function testCallbackButtonDefaultsToDefaultIntent(): void
    {
        $b = CallbackButton::fromJson(['text' => 'X']);
        self::assertSame(Intent::Default, $b->intent);
    }

    public function testRequestContactButtonRoundTrip(): void
    {
        $b = RequestContactButton::fromJson(['text' => 'Send']);
        self::assertSame(ButtonType::Contact, $b->getType());
        self::assertSame('Send', $b->getText());
        self::assertSame(['type' => 'request_contact', 'text' => 'Send'], $b->jsonSerialize());
    }

    public function testRequestGeoLocationButtonRoundTrip(): void
    {
        $b = RequestGeoLocationButton::fromJson(['text' => 'Geo', 'quick' => true]);
        self::assertSame(ButtonType::GeoLocation, $b->getType());
        self::assertTrue($b->quick);
        self::assertSame([
            'type' => 'request_geo_location', 'text' => 'Geo', 'quick' => true,
        ], $b->jsonSerialize());
    }

    public function testOpenAppButtonRoundTrip(): void
    {
        $b = OpenAppButton::fromJson([
            'text' => 'Open',
            'web_app' => 'app1',
            'payload' => 'p1',
            'contact_id' => 5,
        ]);
        self::assertSame(ButtonType::OpenApp, $b->getType());
        self::assertSame('app1', $b->webApp);
        self::assertSame('p1', $b->payload);
        self::assertSame(5, $b->contactId);
        self::assertSame([
            'type' => 'open_app', 'text' => 'Open', 'web_app' => 'app1', 'payload' => 'p1', 'contact_id' => 5,
        ], $b->jsonSerialize());
    }

    public function testMessageButtonRoundTrip(): void
    {
        $b = MessageButton::fromJson(['text' => 'Reply']);
        self::assertSame(ButtonType::Message, $b->getType());
        self::assertSame(['type' => 'message', 'text' => 'Reply'], $b->jsonSerialize());
    }

    public function testClipboardButtonRoundTrip(): void
    {
        $b = ClipboardButton::fromJson(['text' => 'Copy', 'payload' => 'clip']);
        self::assertSame(ButtonType::Clipboard, $b->getType());
        self::assertSame([
            'type' => 'clipboard', 'text' => 'Copy', 'payload' => 'clip',
        ], $b->jsonSerialize());
    }

    public function testButtonGenericRoundTrip(): void
    {
        $b = Button::fromJson(['type' => 'link', 'text' => 'X']);
        self::assertSame(ButtonType::Link, $b->getType());
        self::assertSame(['type' => 'link', 'text' => 'X'], $b->jsonSerialize());
    }

    public function testButtonParserDispatchesAllTypes(): void
    {
        $cases = [
            'link' => LinkButton::class,
            'callback' => CallbackButton::class,
            'request_contact' => RequestContactButton::class,
            'request_geo_location' => RequestGeoLocationButton::class,
            'open_app' => OpenAppButton::class,
            'message' => MessageButton::class,
            'clipboard' => ClipboardButton::class,
        ];
        foreach ($cases as $type => $class) {
            $b = ButtonParser::fromJson(['type' => $type, 'text' => 'X']);
            self::assertInstanceOf($class, $b, $type);
        }
    }

    public function testButtonParserReturnsNullForUnknownType(): void
    {
        self::assertNull(ButtonParser::fromJson(['type' => 'unknown']));
        self::assertNull(ButtonParser::fromJson([]));
    }

    public function testPhotoAttachmentRoundTrip(): void
    {
        $a = PhotoAttachment::fromJson(['type' => 'image', 'payload' => ['photo_id' => 1, 'token' => 't', 'url' => 'u']]);
        self::assertSame(AttachmentType::Image, $a->getType());
        self::assertSame(1, $a->payload->photoId);
        self::assertSame(['type' => 'image', 'payload' => ['photo_id' => 1, 'token' => 't', 'url' => 'u']], $a->jsonSerialize());
    }

    public function testAudioAttachmentRoundTrip(): void
    {
        $a = AudioAttachment::fromJson(['type' => 'audio', 'payload' => ['url' => 'u', 'token' => 't']]);
        self::assertSame(AttachmentType::Audio, $a->getType());
        self::assertSame('t', $a->payload->token);
    }

    public function testVideoAttachmentRoundTrip(): void
    {
        $a = VideoAttachment::fromJson(['type' => 'video', 'payload' => ['url' => 'u', 'token' => 't']]);
        self::assertSame(AttachmentType::Video, $a->getType());
    }

    public function testFileAttachmentRoundTrip(): void
    {
        $a = FileAttachment::fromJson(['type' => 'file', 'payload' => ['url' => 'u', 'token' => 't'], 'filename' => 'f.bin', 'size' => 100]);
        self::assertSame(AttachmentType::File, $a->getType());
        self::assertSame('f.bin', $a->filename);
        self::assertSame(100, $a->size);
        self::assertSame('t', $a->payload->token);
    }

    public function testContactAttachmentRoundTrip(): void
    {
        $a = ContactAttachment::fromJson([
            'type' => 'contact',
            'payload' => [
                'vcf_info' => 'vcf',
                'max_info' => ['user_id' => 1, 'name' => 'X'],
                'hash' => 'h1',
            ],
        ]);
        self::assertSame(AttachmentType::Contact, $a->getType());
        self::assertSame('vcf', $a->payload->vcfInfo);
        self::assertSame(1, $a->payload->maxInfo?->userId);
        self::assertSame('h1', $a->payload->hash);
    }

    public function testStickerAttachmentRoundTrip(): void
    {
        $a = StickerAttachment::fromJson([
            'type' => 'sticker',
            'payload' => ['url' => 'u', 'code' => 'c'],
            'width' => 100,
            'height' => 200,
        ]);
        self::assertSame(AttachmentType::Sticker, $a->getType());
        self::assertSame('c', $a->payload->code);
        self::assertSame(100, $a->width);
        self::assertSame(200, $a->height);
    }

    public function testLocationAttachmentRoundTrip(): void
    {
        $a = LocationAttachment::fromJson(['type' => 'location', 'latitude' => 1.5, 'longitude' => 2.5]);
        self::assertSame(AttachmentType::Location, $a->getType());
        self::assertSame(1.5, $a->latitude);
        self::assertSame(2.5, $a->longitude);
    }

    public function testShareAttachmentRoundTrip(): void
    {
        $a = ShareAttachment::fromJson(['type' => 'share', 'payload' => ['url' => 'u']]);
        self::assertSame(AttachmentType::Share, $a->getType());
        self::assertSame('u', $a->payload->url);
    }

    public function testInlineKeyboardAttachmentRoundTrip(): void
    {
        $a = InlineKeyboardAttachment::fromJson([
            'type' => 'inline_keyboard',
            'payload' => ['buttons' => [[['type' => 'callback', 'text' => 'X', 'payload' => 'p']]]],
        ]);
        self::assertSame(AttachmentType::InlineKeyboard, $a->getType());
        self::assertInstanceOf(Keyboard::class, $a->payload);
        self::assertSame('X', $a->payload->buttons[0][0]->getText());
    }

    public function testAttachmentParserDispatchesAllTypes(): void
    {
        $cases = [
            'audio' => AudioAttachment::class,
            'video' => VideoAttachment::class,
            'image' => PhotoAttachment::class,
            'file' => FileAttachment::class,
            'contact' => ContactAttachment::class,
            'sticker' => StickerAttachment::class,
            'location' => LocationAttachment::class,
            'share' => ShareAttachment::class,
            'inline_keyboard' => InlineKeyboardAttachment::class,
        ];
        foreach ($cases as $type => $class) {
            $payload = ['type' => $type, 'payload' => []];
            if ($type === 'location') {
                $payload = ['type' => $type, 'latitude' => 0.0, 'longitude' => 0.0];
            }
            $a = AttachmentParser::fromJson($payload);
            self::assertInstanceOf($class, $a, $type);
        }
    }

    public function testAttachmentParserReturnsNullForUnknownType(): void
    {
        self::assertNull(AttachmentParser::fromJson(['type' => 'unknown']));
        self::assertNull(AttachmentParser::fromJson([]));
    }

    public function testRequestAttachmentClasses(): void
    {
        $audio = new AudioAttachmentRequest(new UploadedInfo(token: 'a-tok'));
        self::assertSame(['type' => 'audio', 'payload' => ['token' => 'a-tok']], $audio->jsonSerialize());

        $video = new \Pkirillw\MaxBotApi\Scheme\Attachment\VideoAttachmentRequest(new UploadedInfo(token: 'v-tok'));
        self::assertSame('video', $video->getType()->value);

        $file = new FileAttachmentRequest(new UploadedInfo(token: 'f-tok'));
        self::assertSame('file', $file->getType()->value);

        $photo = new PhotoAttachmentRequest(new PhotoAttachmentRequestPayload(token: 'p-tok'));
        self::assertSame('image', $photo->getType()->value);
        self::assertSame(['type' => 'image', 'payload' => ['token' => 'p-tok']], $photo->jsonSerialize());

        $contact = new ContactAttachmentRequest(new ContactAttachmentRequestPayload(name: 'Alice', contactId: 9, vcfPhone: '+7000'));
        self::assertSame('contact', $contact->getType()->value);
        self::assertSame([
            'type' => 'contact',
            'payload' => ['name' => 'Alice', 'contact_id' => 9, 'vcf_phone' => '+7000'],
        ], $contact->jsonSerialize());

        $sticker = new StickerAttachmentRequest(new StickerAttachmentRequestPayload(code: 'SMILE'));
        self::assertSame('sticker', $sticker->getType()->value);
        self::assertSame(['type' => 'sticker', 'payload' => ['code' => 'SMILE']], $sticker->jsonSerialize());

        $location = new LocationAttachmentRequest(1.5, 2.5);
        self::assertSame('location', $location->getType()->value);
        self::assertSame([
            'type' => 'location', 'latitude' => 1.5, 'longitude' => 2.5,
        ], $location->jsonSerialize());

        $kb = new Keyboard();
        $inlineKb = new InlineKeyboardAttachmentRequest($kb);
        self::assertSame('inline_keyboard', $inlineKb->getType()->value);
    }

    public function testPayloadClassesRoundTrip(): void
    {
        $media = MediaAttachmentPayload::fromJson(['url' => 'u', 'token' => 't']);
        self::assertSame('u', $media->url);
        self::assertSame(['url' => 'u', 'token' => 't'], $media->jsonSerialize());

        $file = FileAttachmentPayload::fromJson(['url' => 'u', 'token' => 't']);
        self::assertSame(['url' => 'u', 'token' => 't'], $file->jsonSerialize());

        $photo = PhotoAttachmentPayload::fromJson(['photo_id' => 1, 'token' => 't', 'url' => 'u']);
        self::assertSame(['photo_id' => 1, 'token' => 't', 'url' => 'u'], $photo->jsonSerialize());

        $contact = ContactAttachmentPayload::fromJson(['vcf_info' => 'v']);
        self::assertSame(['vcf_info' => 'v', 'max_info' => null, 'hash' => ''], $contact->jsonSerialize());

        $contactReq = ContactAttachmentRequestPayload::fromJson(['name' => 'A', 'contact_id' => 5]);
        self::assertSame(['name' => 'A', 'contact_id' => 5], $contactReq->jsonSerialize());

        $sticker = StickerAttachmentPayload::fromJson(['url' => 'u', 'code' => 'c']);
        self::assertSame(['url' => 'u', 'code' => 'c'], $sticker->jsonSerialize());

        $stickerReq = StickerAttachmentRequestPayload::fromJson(['code' => 'C']);
        self::assertSame(['code' => 'C'], $stickerReq->jsonSerialize());

        $shared = AttachmentPayload::fromJson(['url' => 'u']);
        self::assertSame(['url' => 'u'], $shared->jsonSerialize());

        $uploaded = UploadedInfo::fromJson(['file_id' => 1, 'token' => 't']);
        self::assertSame(['file_id' => 1, 'token' => 't'], $uploaded->jsonSerialize());

        $uploadedEmpty = new UploadedInfo();
        self::assertSame([], $uploadedEmpty->jsonSerialize());

        $photoToken = PhotoToken::fromJson(['token' => 't1']);
        self::assertSame(['token' => 't1'], $photoToken->jsonSerialize());

        $photoTokens = PhotoTokens::fromJson(['photos' => ['s1' => ['token' => 't1']]]);
        self::assertSame('t1', $photoTokens->photos['s1']->token);
        self::assertSame(['photos' => ['s1' => ['token' => 't1']]], $photoTokens->jsonSerialize());
    }
}
