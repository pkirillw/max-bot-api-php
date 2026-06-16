<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Client;

/**
 * Immutable configuration for the API client.
 *
 * Created via Options::default() and mutated via with*() helpers
 * (every with* returns a new instance, so the same options object can be
 * shared safely across requests).
 *
 * HTTP timeouts are intentionally NOT configured here — PSR-18 has no
 * standard API for them. Configure the timeout on your PSR-18 client
 * implementation (e.g. Guzzle's `timeout` option) before passing it in.
 */
final readonly class Options
{
    public const DEFAULT_BASE_URL = 'https://platform-api.max.ru/';
    public const DEFAULT_API_VERSION = '1.2.5';

    public function __construct(
        public string $baseUrl = self::DEFAULT_BASE_URL,
        public string $version = self::DEFAULT_API_VERSION,
        public bool $debug = false,
        public ?int $debugChatId = null,
        public bool $keepRawUpdates = false,
    ) {}

    public static function default(): self
    {
        return new self();
    }

    public function withBaseUrl(string $baseUrl): self
    {
        return new self(
            baseUrl: $baseUrl,
            version: $this->version,
            debug: $this->debug,
            debugChatId: $this->debugChatId,
            keepRawUpdates: $this->keepRawUpdates,
        );
    }

    public function withVersion(string $version): self
    {
        return new self(
            baseUrl: $this->baseUrl,
            version: $version,
            debug: $this->debug,
            debugChatId: $this->debugChatId,
            keepRawUpdates: $this->keepRawUpdates,
        );
    }

    public function withDebug(bool $debug = true): self
    {
        return new self(
            baseUrl: $this->baseUrl,
            version: $this->version,
            debug: $debug,
            debugChatId: $this->debugChatId,
            keepRawUpdates: $this->keepRawUpdates,
        );
    }

    public function withDebugChatId(int $chatId): self
    {
        return new self(
            baseUrl: $this->baseUrl,
            version: $this->version,
            debug: $this->debug,
            debugChatId: $chatId,
            keepRawUpdates: $this->keepRawUpdates,
        );
    }

    public function withKeepRawUpdates(bool $keep = true): self
    {
        return new self(
            baseUrl: $this->baseUrl,
            version: $this->version,
            debug: $this->debug,
            debugChatId: $this->debugChatId,
            keepRawUpdates: $keep,
        );
    }
}
