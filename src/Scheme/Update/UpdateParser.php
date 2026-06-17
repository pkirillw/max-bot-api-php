<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Update;

use Pkirillw\MaxBotApi\Scheme\Enum\UpdateType;

/**
 * Factory that converts raw webhook JSON into a concrete UpdateInterface implementation.
 */
final class UpdateParser
{
    /**
     * @param string $json raw JSON payload from MAX webhook
     */
    public static function fromJsonString(string $json, bool $keepDebugRaw = false): ?UpdateInterface
    {
        try {
            $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \Pkirillw\MaxBotApi\Exception\UpdateParsingException(
                'invalid webhook JSON: ' . $e->getMessage(),
                0,
                $e,
            );
        }
        if (!is_array($data)) {
            return null;
        }
        return self::fromArray($data, $keepDebugRaw ? $json : '');
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data, string $debugRaw = ''): ?UpdateInterface
    {
        $type = UpdateType::tryFrom((string) ($data['update_type'] ?? ''));
        $timestamp = (int) ($data['timestamp'] ?? 0);
        return match ($type) {
            UpdateType::MessageCallback => MessageCallbackUpdate::fromJson($data, $debugRaw),
            UpdateType::MessageCreated => MessageCreatedUpdate::fromJson($data, $debugRaw),
            UpdateType::MessageRemoved => MessageRemovedUpdate::fromJson($data, $debugRaw),
            UpdateType::MessageEdited => MessageEditedUpdate::fromJson($data, $debugRaw),
            UpdateType::BotAdded => BotAddedToChatUpdate::fromJson($data, $debugRaw),
            UpdateType::BotRemoved => BotRemovedFromChatUpdate::fromJson($data, $debugRaw),
            UpdateType::BotStoped => BotStopedFromChatUpdate::fromJson($data, $debugRaw),
            UpdateType::DialogRemoved => DialogRemovedFromChatUpdate::fromJson($data, $debugRaw),
            UpdateType::DialogCleared => DialogClearedFromChatUpdate::fromJson($data, $debugRaw),
            UpdateType::UserAdded => UserAddedToChatUpdate::fromJson($data, $debugRaw),
            UpdateType::UserRemoved => UserRemovedFromChatUpdate::fromJson($data, $debugRaw),
            UpdateType::BotStarted => BotStartedUpdate::fromJson($data, $debugRaw),
            UpdateType::ChatTitleChanged => ChatTitleChangedUpdate::fromJson($data, $debugRaw),
            default => null,
        };
    }
}
