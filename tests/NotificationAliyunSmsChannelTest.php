<?php

namespace GrantHolle\Tests\Notifications;

use AlibabaCloud\Client\Result\Result;
use AlibabaCloud\Dysmsapi\V20170525\SendSms;
use GrantHolle\Notifications\Channels\AliyunSmsChannel;
use GrantHolle\Notifications\Messages\AliyunMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;

class NotificationAliyunSmsChannelTest extends TestCase
{
    public function testAlwaysToWorks()
    {
        $notification = new NotificationAliyunChannelTestNotification;
        $notifiable = new NotificationAliyunChannelTestNotifiable;
        $alwaysTo = '11223344';
        AliyunSmsChannel::alwaysTo($alwaysTo);

        $result = $this->mock(Result::class, function (MockInterface $mock) {

            $mock->shouldReceive('get')
                ->with('Code')
                ->andReturn('OK');

        });

        $this->mock(SendSms::class, function (MockInterface $mock) use ($alwaysTo, $result) {

            $mock->shouldReceive('withSignName')
                 ->andReturn($mock);

            $mock->shouldReceive('withPhoneNumbers')
                 ->andReturnUsing(function ($phoneNumbers) use ($alwaysTo, $mock) {
                     $this->assertEquals($alwaysTo, $phoneNumbers);

                     return $mock;
                 });

            $mock->shouldReceive('withTemplateParam')
                 ->andReturn($mock);

            $mock->shouldReceive('withTemplateCode')
                 ->andReturn($mock);

            $mock->shouldReceive('request')
                ->andReturn($result);

        });

        $channel = new AliyunSmsChannel('sign-name');
        $channel->send($notifiable, $notification);

        AliyunSmsChannel::alwaysTo(null);
    }

    public function testSmsIsSentViaAliyun()
    {
        $notification = new NotificationAliyunChannelTestNotification;
        $notifiable = new NotificationAliyunChannelTestNotifiable;
        $message = $notification->toAliyunSms($notifiable);

        $result = $this->mock(Result::class, function (MockInterface $mock) {

            $mock->shouldReceive('get')
                 ->with('Code')
                 ->andReturn('OK');

        });

        $this->mock(SendSms::class, function (MockInterface $mock) use ($notifiable, $message, $result) {

            $mock->shouldReceive('withSignName')
                 ->andReturn($mock);

            $mock->shouldReceive('withPhoneNumbers')
                 ->andReturnUsing(function ($phoneNumbers) use ($notifiable, $mock) {
                     $this->assertEquals($notifiable->phone_number, $phoneNumbers);

                     return $mock;
                 });

            $mock->shouldReceive('withTemplateCode')
                 ->andReturnUsing(function ($template) use ($message, $mock) {
                     $this->assertEquals($message->template, $template);

                     return $mock;
                 });

            $mock->shouldReceive('withTemplateParam')
                 ->andReturnUsing(function ($param) use ($message, $mock) {
                     $this->assertEquals(json_encode($message->data), $param);

                     return $mock;
                 });

            $mock->shouldReceive('request')
                 ->andReturn($result);

        });

        $channel = new AliyunSmsChannel('sign-name');
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
