# Changelog

Все заметные изменения этого проекта документируются здесь.

Формат основан на [Keep a Changelog](https://keepachangelog.com/ru/1.1.0/),
версионирование — [SemVer](https://semver.org/lang/ru/spec/v2.0.0.html).

## [Unreleased]

### Added
- Первоначальный перенос `max-bot-api-client-go` на PHP 8.2+.
- DTO для всех схем MAX API (User, Chat, Message, Attachment, Button, Update и др.).
- HTTP-клиент на PSR-18/PSR-17 с retry на `attachment.not.ready`.
- Webhook handler и middleware (PSR-15).
- Builder'ы `Message`, `Keyboard`, `KeyboardRow`.
- Примеры для Slim 4 и чистого PHP.
- PHPStan 2.x на уровне 6, интегрирован в CI.
- `Builder\Message::setBotToken()` для прокси-запросов к другим ботам.

### Fixed
- `Messages::send()/editMessage()` теперь реально спят секунды между попытками, а не микросекунды.
- `Uploads::decode()` больше не теряет токены фото — все варианты размеров парсятся из ответа сервера.
- `Uploads::buildMultipartBody()` экранирует `"` в имени файла (RFC 7578).
- `WebhookHandler` сравнивает секрет через `hash_equals` (защита от timing attack).
- Пустой токен в конструкторе `Client` теперь бросает `EmptyTokenException`, а не `InvalidUrlException`.
- `uploadMediaFromUrl`/`uploadPhotoFromUrl` используют PSR-18 клиент вместо `file_get_contents`.

### Changed
- `Api::withOptions()` переименован в `Api::setOptions()` (метод мутировал состояние).
- Конструктор `Api` теперь дополнительно принимает PSR-18 клиент и PSR-17 request factory.
- `Uploads` конструктор дополнительно принимает PSR-18 и PSR-17 request factory.
- Из `Options` удалены `timeoutSeconds`, `withTimeout()`, `DEFAULT_TIMEOUT_SECONDS` — настройка таймаута остаётся на стороне PSR-18 реализации.
- Добавлена зависимость `psr/http-server-middleware`.

### Removed
- `InvalidUrlException` (не использовался нигде, кроме выброшенного места).
- `Options::timeoutSeconds` (см. Changed).
