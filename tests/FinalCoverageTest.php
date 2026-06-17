<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Api;
use Pkirillw\MaxBotApi\Client\ClientFactory;
use Pkirillw\MaxBotApi\Client\Options;
use Pkirillw\MaxBotApi\Exception\EmptyTokenException;
use Pkirillw\MaxBotApi\Exception\MaxBotApiException;
use Pkirillw\MaxBotApi\Scheme\Attachment\AudioAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\UploadedInfo;
use Pkirillw\MaxBotApi\Scheme\Button\Button;
use Pkirillw\MaxBotApi\Scheme\Enum\ButtonType;
use Pkirillw\MaxBotApi\Scheme\Enum\UpdateType;
use Pkirillw\MaxBotApi\Scheme\Update\MessageCallbackUpdate;
use Pkirillw\MaxBotApi\Tests\Support\CaptureHttp;
use Pkirillw\MaxBotApi\Webhook\WebhookHandler;

final class FinalCoverageTest extends TestCase
{
    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    public function testApiCreateBuildsAllEndpoints(): void
    {
        $http = CaptureHttp::make($this->factory);
        $api = Api::create('tok', $http, $this->factory, $this->factory);

        self::assertInstanceOf(\Pkirillw\MaxBotApi\Endpoint\Bots::class, $api->bots);
        self::assertInstanceOf(\Pkirillw\MaxBotApi\Endpoint\Chats::class, $api->chats);
        self::assertInstanceOf(\Pkirillw\MaxBotApi\Endpoint\Debugs::class, $api->debugs);
        self::assertInstanceOf(\Pkirillw\MaxBotApi\Endpoint\Messages::class, $api->messages);
        self::assertInstanceOf(\Pkirillw\MaxBotApi\Endpoint\Subscriptions::class, $api->subscriptions);
        self::assertInstanceOf(\Pkirillw\MaxBotApi\Endpoint\Uploads::class, $api->uploads);
    }

    public function testApiCreateWithCustomOptions(): void
    {
        $http = CaptureHttp::make($this->factory);
        $options = Options::default()->withDebugChatId(99);
        $api = Api::create('tok', $http, $this->factory, $this->factory, $options);

        self::assertSame(99, $api->debugs->getChatId());
    }

    public function testApiCreateThrowsOnEmptyToken(): void
    {
        $this->expectException(EmptyTokenException::class);
        Api::create('', CaptureHttp::make($this->factory), $this->factory, $this->factory);
    }

    public function testApiSetOptionsAppliesToClient(): void
    {
        $http = CaptureHttp::make($this->factory);
        $api = Api::create('tok', $http, $this->factory, $this->factory);
        $newOpts = Options::default()->withVersion('9.9');
        $api->setOptions($newOpts);

        self::assertSame('9.9', $api->client->getOptions()->version);
    }

    public function testClientFactoryCreateReturnsClient(): void
    {
        $client = ClientFactory::create('tok', CaptureHttp::make($this->factory), $this->factory, $this->factory);
        self::assertNotNull($client);
        $client2 = ClientFactory::create('tok-2', CaptureHttp::make($this->factory), $this->factory, $this->factory);
        self::assertSame('https://platform-api.max.ru/', $client2->getOptions()->baseUrl);
    }

    public function testClientFactoryThrowsOnEmptyToken(): void
    {
        $this->expectException(EmptyTokenException::class);
        ClientFactory::create('', CaptureHttp::make($this->factory), $this->factory, $this->factory);
    }

    public function testClientFactoryWithExplicitOptions(): void
    {
        $opts = Options::default()->withVersion('2.0');
        $client = ClientFactory::create('tok', CaptureHttp::make($this->factory), $this->factory, $this->factory, $opts);
        self::assertSame('2.0', $client->getOptions()->version);
    }

    public function testAudioAttachmentRequestGetType(): void
    {
        $req = new AudioAttachmentRequest(new UploadedInfo(token: 't'));
        self::assertSame(\Pkirillw\MaxBotApi\Scheme\Enum\AttachmentType::Audio, $req->getType());
    }

    public function testButtonGetType(): void
    {
        $b = new Button(type: ButtonType::Link);
        self::assertSame(ButtonType::Link, $b->getType());
    }

    public function testMessageCallbackUpdateGetUpdateType(): void
    {
        $update = MessageCallbackUpdate::fromJson([
            'callback' => ['callback_id' => 'cb', 'user' => ['user_id' => 1, 'name' => 'X']],
        ]);
        self::assertSame(UpdateType::MessageCallback, $update->getUpdateType());
    }

    public function testUploadsParseInvalidJsonPhotoResponse(): void
    {
        $http = CaptureHttp::make($this->factory);
        $http->enqueue(200, '{"url":"https://up.local/put","token":"tok"}');
        $http->enqueue(200, '{not-valid-json');

        $uploads = new \Pkirillw\MaxBotApi\Endpoint\Uploads(
            new \Pkirillw\MaxBotApi\Client\Client('tok', Options::default(), $http, $this->factory, $this->factory),
            $http,
            $this->factory,
        );

        $this->expectException(MaxBotApiException::class);
        $this->expectExceptionMessage('failed to decode photo upload response');
        $uploads->uploadPhotoFromBytes('data', 'pic.png');
    }

    public function testUploadsParsePhotoResponseWithEmptyPhotos(): void
    {
        $http = CaptureHttp::make($this->factory);
        $http->enqueue(200, '{"url":"https://up.local/put","token":"tok"}');
        $http->enqueue(200, '{"photos":{}}');

        $uploads = new \Pkirillw\MaxBotApi\Endpoint\Uploads(
            new \Pkirillw\MaxBotApi\Client\Client('tok', Options::default(), $http, $this->factory, $this->factory),
            $http,
            $this->factory,
        );

        $tokens = $uploads->uploadPhotoFromBytes('data', 'pic.png');
        self::assertSame([], $tokens->photos);
    }

    public function testUploadsFilenameFromUrlWithMalformedUrl(): void
    {
        // parse_url with malformed URL may return false; ensure uploadMediaFromUrl
        // still flows (filename defaults to '').
        $http = CaptureHttp::make($this->factory);
        $http->enqueue(200, '{"url":"https://up.local/put","token":"tok"}');
        $http->enqueue(200, '');

        $fetcher = $this->createMock(\Psr\Http\Client\ClientInterface::class);
        $fetcher->method('sendRequest')->willReturnCallback(
            function ($request) {
                $resp = $this->factory->createResponse(200);
                $resp->getBody()->write('img');
                return $resp;
            },
        );

        $uploads = new \Pkirillw\MaxBotApi\Endpoint\Uploads(
            new \Pkirillw\MaxBotApi\Client\Client('tok', Options::default(), $http, $this->factory, $this->factory),
            $fetcher,
            $this->factory,
        );

        // URL with no path — filenameFromUrl returns ''
        $info = $uploads->uploadMediaFromUrl(\Pkirillw\MaxBotApi\Scheme\Enum\UploadType::Audio, 'https://example.com');
        self::assertSame('tok', $info->token);
        $body = (string) $http->requests[1]->getBody();
        self::assertStringContainsString('filename="file"', $body);
    }

    public function testWebhookHandlerThrowsUpdateParsingExceptionOnParserFailure(): void
    {
        $handler = new WebhookHandler($this->factory, secret: '');

        $request = $this->factory->createServerRequest('POST', '/wh');
        $request->getBody()->write('{not-valid-json');

        $this->expectException(\Pkirillw\MaxBotApi\Exception\UpdateParsingException::class);
        $handler->handle($request);
    }
}
