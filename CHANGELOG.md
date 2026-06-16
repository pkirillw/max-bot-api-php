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
- Тесты для парсера обновлений и билдеров.
- Примеры для Slim 4 и чистого PHP.
