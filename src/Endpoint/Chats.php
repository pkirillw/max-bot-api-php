<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Endpoint;

use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Scheme\Chat;
use Pkirillw\MaxBotApi\Scheme\ChatList;
use Pkirillw\MaxBotApi\Scheme\ChatMember;
use Pkirillw\MaxBotApi\Scheme\ChatMembersList;
use Pkirillw\MaxBotApi\Scheme\ChatPatch;
use Pkirillw\MaxBotApi\Scheme\PinMessageBody;
use Pkirillw\MaxBotApi\Scheme\SimpleQueryResult;
use Pkirillw\MaxBotApi\Scheme\UserIdsList;
use Pkirillw\MaxBotApi\Scheme\Enum\SenderAction;

/**
 * /chats endpoints — chats the bot participates in.
 */
final readonly class Chats
{
    public function __construct(private Client $client)
    {
    }

    public function getChats(int $count = 0, int $marker = 0): ChatList
    {
        $query = array_filter([
            'count' => $count > 0 ? $count : null,
            'marker' => $marker > 0 ? $marker : null,
        ]);
        $data = $this->client->requestJson('GET', 'chats', $query);
        return ChatList::fromJson($data);
    }

    public function getChat(int $chatId): Chat
    {
        $data = $this->client->requestJson('GET', sprintf('chats/%d', $chatId));
        return Chat::fromJson($data);
    }

    public function getChatMembership(int $chatId): ChatMember
    {
        $data = $this->client->requestJson('GET', sprintf('chats/%d/members/me', $chatId));
        return ChatMember::fromJson($data);
    }

    public function getChatMembers(int $chatId, int $count = 0, int $marker = 0): ChatMembersList
    {
        $query = array_filter([
            'count' => $count > 0 ? $count : null,
            'marker' => $marker > 0 ? $marker : null,
        ]);
        $data = $this->client->requestJson('GET', sprintf('chats/%d/members', $chatId), $query);
        return ChatMembersList::fromJson($data);
    }

    /**
     * @param list<int> $userIds
     */
    public function getSpecificChatMembers(int $chatId, array $userIds): ChatMembersList
    {
        $query = ['user_ids' => implode(',', array_map('intval', $userIds))];
        $data = $this->client->requestJson('GET', sprintf('chats/%d/members', $chatId), $query);
        return ChatMembersList::fromJson($data);
    }

    public function getChatAdmins(int $chatId): ChatMembersList
    {
        $data = $this->client->requestJson('GET', sprintf('chats/%d/members/admins', $chatId));
        return ChatMembersList::fromJson($data);
    }

    public function leaveChat(int $chatId): SimpleQueryResult
    {
        $data = $this->client->requestJson('DELETE', sprintf('chats/%d/members/me', $chatId));
        return SimpleQueryResult::fromJson($data);
    }

    public function editChat(int $chatId, ChatPatch $patch): Chat
    {
        $data = $this->client->requestJson('PATCH', sprintf('chats/%d', $chatId), [], $patch);
        return Chat::fromJson($data);
    }

    public function addMember(int $chatId, UserIdsList $users): SimpleQueryResult
    {
        $data = $this->client->requestJson('POST', sprintf('chats/%d/members', $chatId), [], $users);
        return SimpleQueryResult::fromJson($data);
    }

    public function removeMember(int $chatId, int $userId): SimpleQueryResult
    {
        $data = $this->client->requestJson(
            'DELETE',
            sprintf('chats/%d/members', $chatId),
            ['user_id' => $userId],
        );
        return SimpleQueryResult::fromJson($data);
    }

    public function sendAction(int $chatId, SenderAction $action): SimpleQueryResult
    {
        $data = $this->client->requestJson(
            'POST',
            sprintf('chats/%d/actions', $chatId),
            [],
            ['action' => $action->value],
        );
        return SimpleQueryResult::fromJson($data);
    }

    public function pinMessage(int $chatId, PinMessageBody $body): SimpleQueryResult
    {
        $data = $this->client->requestJson(
            'PUT',
            sprintf('chats/%d/pin', $chatId),
            [],
            $body,
        );
        return SimpleQueryResult::fromJson($data);
    }
}
