<?php

namespace Illuminate\Notifications;

use Nexmo\Client as NexmoClient;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use Mrgoon\AliSms\AliSms;

class NexmoChannelServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Notification::extend('aliyun', function ($app) {
            return new Channels\AliyunSmsChannel(new AliSms());
        });
    }
}
