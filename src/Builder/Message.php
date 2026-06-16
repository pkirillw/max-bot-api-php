<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Builder;

use Pkirillw\MaxBotApi\Scheme\Attachment\AttachmentRequestInterface;
use Pkirillw\MaxBotApi\Scheme\Attachment\AudioAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\ContactAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\ContactAttachmentRequestPayload;
use Pkirillw\MaxBotApi\Scheme\Attachment\FileAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\InlineKeyboardAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\LocationAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoAttachmentRequestPayload;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoTokens;
use Pkirillw\MaxBotApi\Scheme\Attachment\StickerAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Attachment\StickerAttachmentRequestPayload;
use Pkirillw\MaxBotApi\Scheme\Attachment\UploadedInfo;
use Pkirillw\MaxBotApi\Scheme\Attachment\VideoAttachmentRequest;
use Pkirillw\MaxBotApi\Scheme\Enum\Format;
use Pkirillw\MaxBotApi\Scheme\Enum\MarkupType;
use Pkirillw\MaxBotApi\Scheme\Enum\MessageLinkType;
use Pkirillw\MaxBotApi\Scheme\MarkUp;
use Pkirillw\MaxBotApi\Scheme\Message as SchemeMessage;
use Pkirillw\MaxBotApi\Scheme\NewMessageBody;
use Pkirillw\MaxBotApi\Scheme\NewMessageLink;

/**
 * Fluent builder for outgoing messages. Mirrors the Go SDK's Message helper
 * with the same setter names so porting code is mechanical.
 */
final class Message
{
    private int $userId = 0;
    private int $chatId = 0;
    private bool $reset = false;
    private bool $disableLinkPreview = false;

    /** @var list<AttachmentRequestInterface> */
    private array $attachments = [];

    private string $text = '';
    private ?Format $format = null;
    private bool $notify = false;
    private ?NewMessageLink $link = null;
    private string $botToken = '';

    /** @var list<string> */
    private array $phoneNumbers = [];

    /** @var list<MarkUp> */
    private array $markups = [];

    public static function new(): self
    {
        return new self();
    }

    public function setUser(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function setChat(int $chatId): self
    {
        $this->chatId = $chatId;
        return $this;
    }

    public function setReset(bool $reset): self
    {
        $this->reset = $reset;
        return $this;
    }

    public function setDisableLinkPreview(bool $disable): self
    {
        $this->disableLinkPreview = $disable;
        return $this;
    }

    public function setBotToken(string $botToken): self
    {
        $this->botToken = $botToken;
        return $this;
    }

    /**
     * @param list<string> $phoneNumbers
     */
    public function setPhoneNumbers(array $phoneNumbers): self
    {
        $this->phoneNumbers = $phoneNumbers;
        return $this;
    }

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function setFormat(Format $format): self
    {
        $this->format = $format;
        return $this;
    }

    public function setNotify(bool $notify): self
    {
        $this->notify = $notify;
        return $this;
    }

    /**
     * Mark this message as a forward of $messageId. Clears the text — forwards carry no body.
     */
    public function setForward(string $messageId): self
    {
        $this->text = '';
        $this->link = new NewMessageLink(type: MessageLinkType::Forward, mid: $messageId);
        return $this;
    }

    public function setReply(string $text, string $messageId): self
    {
        $this->text = $text;
        $this->link = new NewMessageLink(type: MessageLinkType::Reply, mid: $messageId);
        return $this;
    }

    /**
     * Same as setReply but derives chat/user ids from the incoming message.
     */
    public function reply(string $text, SchemeMessage $to): self
    {
        if ($to->recipient?->userId !== null && $to->recipient->userId !== 0) {
            $this->userId = $to->recipient->userId;
        }
        if ($to->recipient?->chatId !== null && $to->recipient->chatId !== 0) {
            $this->chatId = $to->recipient->chatId;
        }
        $mid = $to->body?->mid ?? '';
        return $this->setReply($text, $mid);
    }

    public function addMarkUp(int $userId, int $from, int $length): self
    {
        $this->markups[] = new MarkUp(
            from: $from,
            length: $length,
            userId: $userId,
            type: MarkupType::User,
        );
        return $this;
    }

    public function addKeyboard(Keyboard $keyboard): self
    {
        $this->attachments[] = new InlineKeyboardAttachmentRequest(payload: $keyboard->build());
        return $this;
    }

    public function addPhoto(PhotoTokens $tokens): self
    {
        $this->attachments[] = new PhotoAttachmentRequest(
            payload: new PhotoAttachmentRequestPayload(photos: $tokens->photos),
        );
        return $this;
    }

    public function addPhotoByToken(string $token): self
    {
        $this->attachments[] = new PhotoAttachmentRequest(
            payload: new PhotoAttachmentRequestPayload(token: $token),
        );
        return $this;
    }

    public function addAudio(UploadedInfo $info): self
    {
        $this->attachments[] = new AudioAttachmentRequest(payload: $info);
        return $this;
    }

    public function addVideo(UploadedInfo $info): self
    {
        $this->attachments[] = new VideoAttachmentRequest(payload: $info);
        return $this;
    }

    public function addFile(UploadedInfo $info): self
    {
        $this->attachments[] = new FileAttachmentRequest(payload: $info);
        return $this;
    }

    public function addLocation(float $latitude, float $longitude): self
    {
        $this->attachments[] = new LocationAttachmentRequest(latitude: $latitude, longitude: $longitude);
        return $this;
    }

    public function addContact(string $name, int $contactId, string $vcfInfo = '', string $vcfPhone = ''): self
    {
        $this->attachments[] = new ContactAttachmentRequest(
            payload: new ContactAttachmentRequestPayload(
                name: $name,
                contactId: $contactId,
                vcfInfo: $vcfInfo,
                vcfPhone: $vcfPhone,
            ),
        );
        return $this;
    }

    public function addSticker(string $code): self
    {
        $this->attachments[] = new StickerAttachmentRequest(
            payload: new StickerAttachmentRequestPayload(code: $code),
        );
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function isReset(): bool
    {
        return $this->reset;
    }

    public function isDisableLinkPreview(): bool
    {
        return $this->disableLinkPreview;
    }

    public function getBody(): NewMessageBody
    {
        return new NewMessageBody(
            text: $this->text,
            attachments: $this->attachments,
            link: $this->link,
            format: $this->format,
            phoneNumbers: $this->phoneNumbers,
            notify: $this->notify,
            markups: $this->markups,
            botToken: $this->botToken,
        );
    }
}
