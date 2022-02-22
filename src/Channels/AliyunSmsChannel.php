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
     * @var string
     */
    protected $signName;

    /**
     * @var string|null;
     */
    protected static $alwaysTo;

    /**
     * Create a new Aliyun SMS channel instance.
     *
     * @param string $signName
     */
    public function __construct(string $signName)
    {
        $this->signName = $signName;
    }

    public static function alwaysTo($alwaysTo)
    {
        static::$alwaysTo = $alwaysTo;
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

        if (static::$alwaysTo !== null) {
            $to = static::$alwaysTo;
        }

        /** @var AliyunMessage $message */
        $message = $notification->toAliyunSms($notifiable);

        /** @var SendSms $aliyun */
        $aliyun = resolve(SendSms::class);
        $aliyun->withSignName($this->signName);

        if (filled($message->data)) {
            $aliyun->withTemplateParam(json_encode($message->data));
        }

        $res = $aliyun
            ->withPhoneNumbers($to)
            ->withTemplateCode($message->template)
            ->request();

        if ($res->get('Code') !== 'OK') {
            throw new AliyunSmsException($res->get('Message') . ' (' . $res->get('Code') . ')');
        }

        return $res;
    }
}
