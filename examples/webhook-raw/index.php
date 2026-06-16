<?php

declare(strict_types=1);

/**
 * Minimal webhook example using native PHP + Guzzle.
 *
 * Useful for shared hosting where you cannot run Slim/Mezzio. Run behind any
 * PHP-capable web server; map /webhook to this file.
 */

require __DIR__ . '/../../vendor/autoload.php';

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr17\HttpFactory;
use Pkirillw\MaxBotApi\Api;
use Pkirillw\MaxBotApi\Builder\Keyboard;
use Pkirillw\MaxBotApi\Builder\Message as MessageBuilder;
use Pkirillw\MaxBotApi\Client\Options;
use Pkirillw\MaxBotApi\Scheme\Update\MessageCreatedUpdate;
use Pkirillw\MaxBotApi\Scheme\Update\UpdateParser;

$token  = getenv('BOT_TOKEN') ?: '';
$secret = getenv('MAX_BOT_API_SECRET') ?: '';

$http          = new GuzzleClient(['timeout' => 30]);
$factory       = new HttpFactory();
$api = Api::create(
    token: $token,
    http: $http,
    requestFactory: $factory,
    streamFactory: $factory,
    options: Options::default()->withDebug(),
);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path !== '/webhook' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(404);
    echo 'not found';
    return;
}

// Verify shared secret to make sure the request really comes from MAX.
if ($secret !== '' && ($_SERVER['HTTP_X_MAX_BOT_API_SECRET'] ?? '') !== $secret) {
    http_response_code(401);
    echo 'invalid secret';
    return;
}

$body = file_get_contents('php://input');
$update = UpdateParser::fromJsonString($body, keepDebugRaw: true);
if ($update === null) {
    http_response_code(400);
    echo 'unknown update type';
    return;
}

if ($update instanceof MessageCreatedUpdate) {
    $keyboard = (new Keyboard())
        ->addRow()->addCallback('Пункт 1', 'item_1')
        ->addCallback('Пункт 2', 'item_2');
    $keyboard->addRow()->addLink('Документация', 'https://dev.max.ru');

    $api->messages->send(
        MessageBuilder::new()
            ->setChat($update->getChatId())
            ->setText('Привет! Выбери пункт:')
            ->addKeyboard($keyboard),
    );
}

http_response_code(200);
echo json_encode(['ok' => true]);
