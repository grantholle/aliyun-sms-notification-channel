<?php

namespace GrantHolle\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Mrgoon\AliSms\AliSms;
use GrantHolle\Notifications\Exceptions\AliyunSmsException;

class AliyunSmsChannel
{
    /**
     * The Nexmo client instance.
     *
     * @var \Mrgoon\AliSms\AliSms
     */
    protected $aliyun;

    /**
     * Create a new Aliyun SMS channel instance.
     *
     * @param  \Mrgoon\AliSms\AliSms  $aliyun
     * @return void
     */
    public function __construct(AliSms $aliyun)
    {
        $this->aliyun = $aliyun;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return mixed
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('aliyun', $notification)) {
            return;
        }

        $message = $notification->toAliyunSms($notifiable);

        $res = $this->aliyun->sendSms(
            $to,
            $message->template,
            $message->data,
            config('services.aliyun-sms')
        );

        if ($res->Code !== 'OK') {
            throw new AliyunSmsException($res->Message . ' (' . $res->Code . ')');
        }

        return $res;
    }
}
