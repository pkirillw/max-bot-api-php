<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Button;

use Pkirillw\MaxBotApi\Scheme\Enum\ButtonType;

interface ButtonInterface extends \JsonSerializable
{
    public function getType(): ButtonType;

    public function getText(): string;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self;
}
