<?php

namespace GrantHolle\Notifications;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Dysmsapi\Dysmsapi;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class AliyunSmsChannelServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     * @throws ClientException
     */
    public function register()
    {
        Notification::resolved(function (ChannelManager $channelManager) {
            $channelManager->extend('aliyun', function ($app) {
                AlibabaCloud::accessKeyClient(
                    $app['config']['services.aliyun-sms.access_key'],
                    $app['config']['services.aliyun-sms.access_secret']
                )
                    ->asDefaultClient()
                    ->regionId('cn-hangzhou');

                Dysmsapi::v20170525();

                return new Channels\AliyunSmsChannel($app['config']['services.aliyun-sms.sign_name']);
            });
        });
    }
}
