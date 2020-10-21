<?php

namespace GrantHolle\Tests\Notifications;

use AlibabaCloud\Client\Result\Result;
use AlibabaCloud\Dysmsapi\V20170525\SendSms;
use GrantHolle\Notifications\Channels\AliyunSmsChannel;
use GrantHolle\Notifications\Messages\AliyunMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery;
use PHPUnit\Framework\TestCase;

class NotificationAliyunSmsChannelTest extends TestCase
{
    public function testSmsIsSentViaAliyun()
    {
        $notification = new NotificationAliyunChannelTestNotification;
        $notifiable = new NotificationAliyunChannelTestNotifiable;
        $message = $notification->toAliyunSms($notifiable);

        $aliyun = Mockery::mock(SendSms::class);
        $result = Mockery::mock(Result::class);
        $result->shouldReceive('get')
               ->with('Code')
               ->andReturn('OK');

        $aliyun->shouldReceive('withPhoneNumbers')
               ->andReturnUsing(function ($phoneNumbers) use ($notifiable, $aliyun) {
                   $this->assertEquals($notifiable->phone_number, $phoneNumbers);

                   return $aliyun;
               });

        $aliyun->shouldReceive('withTemplateCode')
               ->andReturnUsing(function ($template) use ($message, $aliyun) {
                   $this->assertEquals($message->template, $template);

                   return $aliyun;
               });

        $aliyun->shouldReceive('withTemplateParam')
               ->andReturnUsing(function ($param) use ($message, $aliyun) {
                   $this->assertEquals(json_encode($message->data), $param);

                   return $aliyun;
               });

        $aliyun->shouldReceive('request')
               ->andReturn($result);

        $channel = new AliyunSmsChannel($aliyun);
        $channel->send($notifiable, $notification);
    }
}

class NotificationAliyunChannelTestNotifiable
{
    use Notifiable;

    public $phone_number = '5555555555';

    public function routeNotificationForAliyun(Notification $notification)
    {
        return $this->phone_number;
    }
}

class NotificationAliyunChannelTestNotification extends Notification
{
    public function toAliyunSms($notifiable)
    {
        return new AliyunMessage('template_code', [ 'key' => 'value' ]);
    }
}
