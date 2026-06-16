# Development Guide

Окружение для локальной разработки и контрибьюта в `pkirillw/max-bot-api-php`.

## Требования

- PHP ≥ 8.2 (CLI, mbstring, json, curl)
- Composer ≥ 2.0
- (опционально) Docker — для запуска тестов без локального PHP

## Установка

```sh
git clone <repo-url>
cd max-bot-api-php
composer install
```

## Запуск тестов

```sh
vendor/bin/phpunit                    # все тесты
vendor/bin/phpunit --filter=Builder   # один тест-класс
vendor/bin/phpunit --coverage-text    # с покрытием (нужен xdebug)
```

## Линтинг

Рекомендуется PHPStan на уровне 6+:

```sh
composer require --dev phpstan/phpstan
vendor/bin/phpstan analyse src level 6
```

## Стиль кода

- PHP 8.2+ идиомы: readonly classes, native enums, typed properties.
- DTO следуют шаблону: `readonly class` + статический `fromJson(array)` + `jsonSerialize()`.
- Snake_case поля из MAX-JSON маппятся в camelCase PHP-свойства.
- Никаких сеттеров вне `Builder\*` — все DTO иммутабельны.

## Релизы

Релизы ведутся через git-теги SemVer (`v0.1.0`, `v1.2.3`). Packagist автоматически
публикует новый zip при появлении тега — никаких ручных действий, кроме пуша тега, не требуется.

Перед релизом:

1. Все тесты зелёные в CI.
2. `CHANGELOG.md` обновлён.
3. Версия в `Options::DEFAULT_API_VERSION` актуальна.

```sh
git tag v0.1.0
git push origin v0.1.0
```

## Что НЕ коммитить

- `vendor/` — управляется Composer'ом.
- `composer.lock` — это библиотека, не приложение.
- `.phpunit.cache/`, IDE-файлы, `.env`.

См. `.gitignore` и `.gitattributes`.
