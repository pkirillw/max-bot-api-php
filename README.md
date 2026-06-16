# max-bot-api-php

PHP-клиент для **MAX Bot API**. Перенос [max-bot-api-client-go](https://github.com/max-messenger/max-bot-api-client-go)
на PHP 8.2+ с идентичной Surface Area (класс `Api`, группы `Bots` / `Chats` / `Messages` /
`Subscriptions` / `Uploads` / `Debugs`).

## Особенности

- **PHP 8.2+**: readonly classes, native enums, typed properties.
- **PSR-18 / PSR-17**: подключаете любой HTTP-клиент (Guzzle, Symfony, …).
- **PSR-15**: готовый `WebhookHandler` и `WebhookMiddleware`.
- **Webhooks вместо long polling**: парсер webhook-ов приводит «сырой» JSON MAX к
  типизированным `UpdateInterface` (13 типов событий), включая вложенные кнопки и
  вложения.
- **Builder'ы**: `Message`, `Keyboard`, `KeyboardRow` с fluent-интерфейсом, как в Go.
- **Сериализация в обе стороны**: каждый DTO имеет `fromJson()` (где применимо) и
  `jsonSerialize()`.

## Установка

```sh
composer require pkirillw/max-bot-api-php
```

В `suggest` указаны Guzzle и Nyholm PSR-17 — установите их при необходимости:

```sh
composer require guzzlehttp/guzzle guzzlehttp/psr7 nyholm/psr7
```

## Быстрый старт

```php
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Psr17\HttpFactory;
use Pkirillw\MaxBotApi\Api;
use Pkirillw\MaxBotApi\Builder\Message;
use Pkirillw\MaxBotApi\Scheme\Update\MessageCreatedUpdate;

$api = Api::create(
    token: getenv('BOT_TOKEN'),
    http: new Guzzle(['timeout' => 30]),
    requestFactory: new HttpFactory(),
    streamFactory: new HttpFactory(),
);

$info = $api->bots->getBot();
echo $info->name, PHP_EOL;
```

## Webhook (PSR-15)

```php
use Nyholm\Psr17\Factory\Psr17Factory;
use Pkirillw\MaxBotApi\Webhook\WebhookHandler;
use Pkirillw\MaxBotApi\Builder\Message as MessageBuilder;
use Pkirillw\MaxBotApi\Scheme\Update\MessageCreatedUpdate;

$handler = new WebhookHandler(new Psr17Factory(), secret: getenv('MAX_BOT_API_SECRET'));
$handler->setHandler(function (object $update) use ($api): void {
    if ($update instanceof MessageCreatedUpdate) {
        $api->messages->send(
            MessageBuilder::new()
                ->setChat($update->getChatId())
                ->setText('Привет!'),
        );
    }
});

$app->post('/webhook', $handler); // Slim / Mezzio / Nyholm
```

Перед первым запросом подпишите свой URL на стороне MAX:

```php
$api->subscriptions->subscribe(
    'https://your-host/webhook',
    [], // пустой список = все типы апдейтов
    getenv('MAX_BOT_API_SECRET'),
);
```

## Отправка сообщений

```php
use Pkirillw\MaxBotApi\Builder\Keyboard;
use Pkirillw\MaxBotApi\Builder\Message as MessageBuilder;
use Pkirillw\MaxBotApi\Scheme\Enum\Format;
use Pkirillw\MaxBotApi\Scheme\Enum\Intent;

$keyboard = (new Keyboard())
    ->addRow()->addCallback('Да', 'yes', Intent::Positive)
              ->addCallback('Нет', 'no', Intent::Negative);
$keyboard->addRow()->addLink('Документация', 'https://dev.max.ru');

// вернёт созданное сообщение (включая mid)
$message = $api->messages->sendWithResult(
    MessageBuilder::new()
        ->setChat($chatId)
        ->setText('**Готово?**')
        ->setFormat(Format::Markdown)
        ->addKeyboard($keyboard),
);

// редактирование со retry на attachment.not.ready
$api->messages->editMessage($message->body->mid, MessageBuilder::new()->setText('updated'));

// удаление
$api->messages->deleteMessage($message->body->mid);
```

## Uploads

```php
use Pkirillw\MaxBotApi\Scheme\Enum\UploadType;

$photo = $api->uploads->uploadPhotoFromFile('/path/to/pic.png');
$audio = $api->uploads->uploadMediaFromFile(UploadType::Audio, '/path/to/music.mp3');
$file  = $api->uploads->uploadMediaFromUrl(UploadType::File, 'https://example.com/doc.pdf');

$api->messages->send(
    MessageBuilder::new()
        ->setChat($chatId)
        ->setText('смотри:')
        ->addPhoto($photo),
);

$api->messages->send(
    MessageBuilder::new()->setChat($chatId)->addAudio($audio),
);
```

## Обработка ошибок

Все ошибки библиотеки — наследники `Pkirillw\MaxBotApi\Exception\MaxBotApiException`:

| Класс                     | Когда бросается                                              |
|---------------------------|--------------------------------------------------------------|
| `ApiException`            | MAX вернул HTTP не 2xx. Есть `isAttachmentNotReady()`.       |
| `NetworkException`        | Сетевая ошибка (DNS, соединение разорвано).                  |
| `TimeoutException`        | Превышен timeout запроса.                                    |
| `SerializationException`  | Сбой json_encode / json_decode.                              |
| `EmptyTokenException`     | Пустой токен в конструкторе `Api::create()`.                 |
| `UpdateParsingException`  | Не удалось разобрать тело webhook.                           |

`Messages::send()` / `sendWithResult()` / `editMessage()` автоматически повторяют запрос
до 3 раз с экспоненциальной задержкой, когда сервер отвечает `attachment.not.ready`.

## Структура

```
src/
├── Api.php                       главный entrypoint
├── Client/                       PSR-18 transport (Client, ClientFactory, Options)
├── Endpoint/                     Bots, Chats, Messages, Subscriptions, Uploads, Debugs
├── Builder/                      Message, Keyboard, KeyboardRow
├── Webhook/                      PSR-15 WebhookHandler, WebhookMiddleware
├── Exception/                    Все исключения
└── Scheme/                       DTO (read-only классы + enum'ы)
    ├── Enum/                     12 enum'ов (AttachmentType, Intent, …)
    ├── Button/                   ButtonInterface и 9 реализаций
    ├── Attachment/               AttachmentInterface и 23 DTO (входящие + запросы)
    ├── Update/                   UpdateInterface и 13 реализаций + UpdateParser
    └── …                         User, Chat, Message, NewMessageBody, …
```

## Отличия от Go-версии

- Long polling (`GetUpdates` / каналы) **не реализован**. Получение событий — только
  через webhook (PSR-15). Если нужен polling — можно написать свой цикл вокруг
  `Client::requestJson('GET', 'updates', $query)`.
- `Channel<T>`-семантика заменена на PSR-15 middleware / handler.
- `UpdateInterface::getUpdateTime()` возвращает `\DateTimeImmutable|null` (Go — `time.Time`).
- `MessageCreatedUpdate::getCommand()` / `getParam()` работают так же, как в Go.

## Лицензия

Apache-2.0, совместимо с исходной Go-библиотекой.
