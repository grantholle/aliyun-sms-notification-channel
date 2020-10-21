<?php

namespace GrantHolle\Notifications\Channels;

use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Dysmsapi\V20170525\SendSms;
use GrantHolle\Notifications\Exceptions\AliyunSmsException;
use GrantHolle\Notifications\Messages\AliyunMessage;
use Illuminate\Notifications\Notification;

class AliyunSmsChannel
{
    /**
     * @var SendSms
     */
    protected $aliyun;

    /**
     * Create a new Aliyun SMS channel instance.
     *
     * @param SendSms $aliyun
     */
    public function __construct(SendSms $aliyun)
    {
        $this->aliyun = $aliyun;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     *
     * @return mixed
     * @throws AliyunSmsException
     * @throws ClientException
     * @throws ServerException
     */
    public function send($notifiable, Notification $notification)
    {
        if (!$to = $notifiable->routeNotificationFor('aliyun', $notification)) {
            return null;
        }

        /**
         * @var AliyunMessage $message
         */
        $message = $notification->toAliyunSms($notifiable);

        if (filled($message->data)) {
            $this->aliyun->withTemplateParam(json_encode($message->data));
        }

        $res = $this->aliyun->withPhoneNumbers($to)
                            ->withTemplateCode($message->template)
                            ->request();

        if ($res->get('Code') !== 'OK') {
            throw new AliyunSmsException($res->get('Message') . ' (' . $res->get('Code') . ')');
        }

        return $res;
    }
}
