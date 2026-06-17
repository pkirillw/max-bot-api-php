<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Endpoint;

use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Scheme\BotInfo;
use Pkirillw\MaxBotApi\Scheme\BotPatch;

/**
 * /me endpoints — read and patch the current bot.
 */
final readonly class Bots
{
    public function __construct(private Client $client) {}

    public function getBot(): BotInfo
    {
        $data = $this->client->requestJson('GET', 'me');
        return BotInfo::fromJson($data);
    }

    public function patchBot(BotPatch $patch): BotInfo
    {
        $data = $this->client->requestJson('PATCH', 'me', [], $patch);
        return BotInfo::fromJson($data);
    }
}
