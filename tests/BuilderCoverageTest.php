<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Tests;

use PHPUnit\Framework\TestCase;
use Pkirillw\MaxBotApi\Builder\Keyboard;
use Pkirillw\MaxBotApi\Builder\KeyboardRow;
use Pkirillw\MaxBotApi\Builder\Message as MessageBuilder;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoToken;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoTokens;
use Pkirillw\MaxBotApi\Scheme\Attachment\UploadedInfo;
use Pkirillw\MaxBotApi\Scheme\Enum\Format;
use Pkirillw\MaxBotApi\Scheme\Enum\Intent;
use Pkirillw\MaxBotApi\Scheme\Message as SchemeMessage;
use Pkirillw\MaxBotApi\Scheme\Recipient;

final class BuilderCoverageTest extends TestCase
{
    public function testMessageFluentChainSetsAllFields(): void
    {
        $builder = MessageBuilder::new()
            ->setUser(11)
            ->setChat(22)
            ->setReset(true)
            ->setDisableLinkPreview(true)
            ->setText('hi')
            ->setFormat(Format::Html)
            ->setNotify(true)
            ->setPhoneNumbers(['+7000'])
            ->setBotToken('bot-tok');

        self::assertSame(11, $builder->getUserId());
        self::assertSame(22, $builder->getChatId());
        self::assertTrue($builder->isReset());
        self::assertTrue($builder->isDisableLinkPreview());

        $body = $builder->getBody()->jsonSerialize();
        self::assertSame('hi', $body['text']);
        self::assertSame('html', $body['format']);
        self::assertTrue($body['notify']);
        self::assertSame(['+7000'], $body['phone_numbers']);
        self::assertSame('bot-tok', $body['bot_token']);
    }

    public function testMessageAddPhotoAndAddPhotoByToken(): void
    {
        $tokens = new PhotoTokens(['s1' => new PhotoToken('t1')]);
        $builder = MessageBuilder::new()
            ->addPhoto($tokens)
            ->addPhotoByToken('tok-direct');

        $body = $builder->getBody()->jsonSerialize();
        self::assertCount(2, $body['attachments']);
        self::assertSame('t1', $body['attachments'][0]['payload']['photos']['s1']['token']);
        self::assertSame('tok-direct', $body['attachments'][1]['payload']['token']);
    }

    public function testMessageAddAudioVideoFile(): void
    {
        $info = new UploadedInfo(token: 'u-tok');
        $builder = MessageBuilder::new()
            ->addAudio($info)
            ->addVideo($info)
            ->addFile($info);

        $body = $builder->getBody()->jsonSerialize();
        self::assertSame('audio', $body['attachments'][0]['type']);
        self::assertSame('video', $body['attachments'][1]['type']);
        self::assertSame('file', $body['attachments'][2]['type']);
    }

    public function testMessageAddLocation(): void
    {
        $body = MessageBuilder::new()->addLocation(1.5, 2.5)->getBody()->jsonSerialize();
        self::assertSame('location', $body['attachments'][0]['type']);
        self::assertSame(1.5, $body['attachments'][0]['latitude']);
        self::assertSame(2.5, $body['attachments'][0]['longitude']);
    }

    public function testMessageAddContact(): void
    {
        $body = MessageBuilder::new()->addContact('Alice', 9, vcfInfo: 'vcf', vcfPhone: '+7000')->getBody()->jsonSerialize();
        self::assertSame('contact', $body['attachments'][0]['type']);
        self::assertSame('Alice', $body['attachments'][0]['payload']['name']);
        self::assertSame(9, $body['attachments'][0]['payload']['contact_id']);
        self::assertSame('vcf', $body['attachments'][0]['payload']['vcf_info']);
        self::assertSame('+7000', $body['attachments'][0]['payload']['vcf_phone']);
    }

    public function testMessageAddSticker(): void
    {
        $body = MessageBuilder::new()->addSticker('SMILE')->getBody()->jsonSerialize();
        self::assertSame('sticker', $body['attachments'][0]['type']);
        self::assertSame('SMILE', $body['attachments'][0]['payload']['code']);
    }

    public function testMessageAddKeyboard(): void
    {
        $kb = new Keyboard();
        $kb->addRow()->addCallback('Hi', 'p');
        $body = MessageBuilder::new()->addKeyboard($kb)->getBody()->jsonSerialize();
        self::assertSame('inline_keyboard', $body['attachments'][0]['type']);
        self::assertSame('callback', $body['attachments'][0]['payload']['buttons'][0][0]['type']);
    }

    public function testMessageAddMarkUp(): void
    {
        $body = MessageBuilder::new()->setText('Hello @user')->addMarkUp(7, 6, 5)->getBody()->jsonSerialize();
        self::assertCount(1, $body['markup']);
        self::assertSame(7, $body['markup'][0]['user_id']);
        self::assertSame(6, $body['markup'][0]['from']);
        self::assertSame(5, $body['markup'][0]['length']);
        self::assertSame('user_mention', $body['markup'][0]['type']);
    }

    public function testMessageSetReply(): void
    {
        $body = MessageBuilder::new()->setReply('hi back', 'mid-1')->getBody()->jsonSerialize();
        self::assertSame('hi back', $body['text']);
        self::assertSame('reply', $body['link']['type']);
        self::assertSame('mid-1', $body['link']['mid']);
    }

    public function testMessageReplyDerivesFromIncomingMessageWithUserId(): void
    {
        $incoming = new SchemeMessage(
            recipient: new Recipient(userId: 5, chatId: 9),
        );

        $builder = MessageBuilder::new()->reply('hello', $incoming);
        self::assertSame(5, $builder->getUserId());
        self::assertSame(9, $builder->getChatId());
        $body = $builder->getBody()->jsonSerialize();
        self::assertSame('', $body['link']['mid']);
    }

    public function testKeyboardRowChainsAllAdders(): void
    {
        $row = (new KeyboardRow())
            ->addLink('Link', 'https://x')
            ->addCallback('Cb', 'p', Intent::Positive)
            ->addContact('Contact')
            ->addGeolocation('Geo', true)
            ->addOpenApp('App', 'app1', 'p1', 5)
            ->addMessage('Msg')
            ->addClipboard('Clip', 'c');

        $buttons = $row->getButtons();
        self::assertCount(7, $buttons);
        self::assertSame('Link', $buttons[0]->getText());
        self::assertSame('https://x', $buttons[0]->url);
        self::assertSame('Cb', $buttons[1]->getText());
        self::assertSame(Intent::Positive, $buttons[1]->intent);
        self::assertSame('Contact', $buttons[2]->getText());
        self::assertSame('Geo', $buttons[3]->getText());
        self::assertTrue($buttons[3]->quick);
        self::assertSame('App', $buttons[4]->getText());
        self::assertSame('Msg', $buttons[5]->getText());
        self::assertSame('Clip', $buttons[6]->getText());
    }

    public function testKeyboardAddButtonAcceptsCustomButtonInterface(): void
    {
        $custom = new \Pkirillw\MaxBotApi\Scheme\Button\LinkButton(text: 'Custom', url: 'https://y');
        $row = (new KeyboardRow())->addButton($custom);
        self::assertSame($custom, $row->getButtons()[0]);
    }

    public function testKeyboardBuildAndRows(): void
    {
        $kb = new Keyboard();
        self::assertSame([], $kb->getRows());

        $row = $kb->addRow();
        $row->addCallback('A', 'pa');
        $kb->addRow()->addLink('B', 'https://b');

        self::assertCount(2, $kb->getRows());
        $built = $kb->build();
        self::assertCount(2, $built->buttons);
        self::assertSame('A', $built->buttons[0][0]->getText());
        self::assertSame('B', $built->buttons[1][0]->getText());
    }
}
