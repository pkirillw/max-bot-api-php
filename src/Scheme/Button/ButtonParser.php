<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Button;

use Pkirillw\MaxBotApi\Scheme\Enum\ButtonType;

/**
 * Parses raw button JSON into a concrete ButtonInterface implementation based on its type.
 */
final class ButtonParser
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): ?ButtonInterface
    {
        $type = ButtonType::tryFrom((string) ($data['type'] ?? ''));
        return match ($type) {
            ButtonType::Link => LinkButton::fromJson($data),
            ButtonType::Callback => CallbackButton::fromJson($data),
            ButtonType::Contact => RequestContactButton::fromJson($data),
            ButtonType::GeoLocation => RequestGeoLocationButton::fromJson($data),
            ButtonType::OpenApp => OpenAppButton::fromJson($data),
            ButtonType::Message => MessageButton::fromJson($data),
            ButtonType::Clipboard => ClipboardButton::fromJson($data),
            default => null,
        };
    }
}
