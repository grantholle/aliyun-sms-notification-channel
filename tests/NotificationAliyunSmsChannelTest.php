<?php

namespace GrantHolle\Tests\Notifications;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use GrantHolle\Notifications\Messages\AliyunMessage;
use GrantHolle\Notifications\Channels\AliyunSmsChannel;

class NotificationAliyunSmsChannelTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testSmsIsSentViaAliyun()
    {
        $notification = new NotificationAliyunChannelTestNotification;
        $notifiable = new NotificationAliyunChannelTestNotifiable;

        $channel = new AliyunSmsChannel(
            $nexmo = m::mock(Client::class), '4444444444'
        );

        $nexmo->shouldReceive('message->send')->with([
            'type' => 'text',
            'from' => '4444444444',
            'to' => '5555555555',
            'text' => 'this is my message',
        ]);

        $channel->send($notifiable, $notification);
    }

    public function testSmsIsSentViaAliyunWithCustomFrom()
    {
        $notification = new NotificationAliyunChannelTestCustomFromNotification;
        $notifiable = new NotificationAliyunChannelTestNotifiable;

        $channel = new AliyunSmsChannel(
            $nexmo = m::mock(Client::class), '4444444444'
        );

        $nexmo->shouldReceive('message->send')->with([
            'type' => 'unicode',
            'from' => '5554443333',
            'to' => '5555555555',
            'text' => 'this is my message',
        ]);

        $channel->send($notifiable, $notification);
    }
}

class NotificationAliyunChannelTestNotifiable
{
    use Notifiable;

    public $phone_number = '5555555555';
}

class NotificationAliyunChannelTestNotification extends Notification
{
    public function toAliyun($notifiable)
    {
        return new AliyunMessage('this is my message');
    }
}

class NotificationAliyunChannelTestCustomFromNotification extends Notification
{
    public function toAliyun($notifiable)
    {
        return (new AliyunMessage('this is my message'))->from('5554443333')->unicode();
    }
}
