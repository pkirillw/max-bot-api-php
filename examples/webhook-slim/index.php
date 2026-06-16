<?php

declare(strict_types=1);

/**
 * Webhook example using Slim 4 + Nyholm PSR-17.
 *
 *   composer require slim/slim nyholm/psr7 guzzlehttp/guzzle
 *   php -S 0.0.0.0:8080 examples/webhook-slim/index.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use GuzzleHttp\Client as GuzzleClient;
use Nyholm\Psr17\Factory\Psr17Factory;
use Pkirillw\MaxBotApi\Api;
use Pkirillw\MaxBotApi\Builder\Message as MessageBuilder;
use Pkirillw\MaxBotApi\Scheme\Enum\Format;
use Pkirillw\MaxBotApi\Scheme\Update\MessageCallbackUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\MessageCreatedUpdate;
use Pkirillw\MaxBotApi\Webhook\WebhookHandler;
use Slim\Factory\AppFactory;

$token  = getenv('BOT_TOKEN') ?: '';
$secret = getenv('MAX_BOT_API_SECRET') ?: '';

$psr17    = new Psr17Factory();
$guzzle   = new GuzzleClient(['timeout' => 30]);

$api = Api::create(
    token: $token,
    http: $guzzle,
    requestFactory: $psr17,
    streamFactory: $psr17,
);

// Subscribe webhook once. Set SUBSCRIBE_WEBHOOK=1 in the env on first deploy,
// then unset — repeated subscribe calls will register duplicate deliveries.
if (getenv('SUBSCRIBE_WEBHOOK') === '1') {
    $host = getenv('HOST') ?: 'https://your-public-host.example';
    $api->subscriptions->subscribe($host . '/webhook', [], $secret);
}

$app = AppFactory::create(determineResponseFactory: $psr17);

$handler = new WebhookHandler($psr17, $secret, keepRawUpdates: true);
$handler->setHandler(function ($update) use ($api): void {
    if ($update instanceof MessageCreatedUpdate) {
        $chatId = $update->getChatId();
        $text   = sprintf('Привет, %s! Ты написал: %s', $update->message->sender?->name ?? '', $update->getText());

        $api->messages->send(
            MessageBuilder::new()
                ->setChat($chatId)
                ->setText($text)
                ->setFormat(Format::Markdown),
        );
        return;
    }
    if ($update instanceof MessageCallbackUpdate) {
        // ответ на нажатие inline-кнопки
        $api->messages->answerOnCallback(
            $update->callback->callbackId,
            new \Pkirillw\MaxBotApi\Scheme\CallbackAnswer(
                notification: 'Кнопка нажата: ' . $update->callback->payload,
            ),
        );
    }
});

$app->post('/webhook', $handler);
$app->run();
