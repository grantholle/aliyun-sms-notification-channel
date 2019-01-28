# Aliyun SMS Notification Channel

A Laravel notification channel for Aliyun's SMS product.

## Installation

```bash
composer require grantholle/aliyun-sms-notification-channel
```

## Configuration

It's preferred to include sensitive keys and secrets in the `.env` file so that the information is not included in source control.

In your `.env` file, add some keys:

```
ALIYUN_SMS_AK=XXXXXXXXXX
ALIYUN_SMS_AS=XXXXXXXXXX
ALIYUN_SMS_SIGN_NAME=名字
```

In `config/services.php`, add the following:

```php
'aliyun-sms' => [
    'key' => env('ALIYUN_SMS_AK'),
    'secret' => env('ALIYUN_SMS_AS'),
    'sign-name' => env('ALIYUN_SMS_SIGN_NAME'),
],
```

## Usage

### Create the Notification

Generate a new notification for your application.

```bash
php artisan make:notification OrderPaid
```

Add the `aliyun` channel and the `toAliyunSms()` function to generate the Aliyun message. There is the `template()` function to set the template ID of this message, as well as a `data()` function to set the placeholders in the template.

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use GrantHolle\Notifications\Messages\AliyunMessage;
use App\Order;

class OrderPaid extends Notification
{
    protected $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['aliyun'];
    }

    /**
     * Get the Aliyun SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \GrantHolle\Notifications\Messages\AliyunMessage
     */
    public function toAliyunSms($notifiable)
    {
        return (new AliyunMessage)
            ->template('SMS_XXXXXXX')
            ->data([
                'order_no' => $this->order->order_no,
                'total' => $this->order->total,
            ]);
    }
}
```

### Routing the Notification

Following the [documentation](https://laravel.com/docs/notifications#sending-notifications), this assumes we're using the `User` model. We need to add the `Notifiable` trait and the `routeNotificationForAliyun()` function to return the phone number.

```php
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Route notifications for the Aliyun SMS channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForAliyun($notification)
    {
        return $this->phone;
    }
}
```

### Sending the Notification

Now in our application we can send the notification. Refer to the [documentation](https://laravel.com/docs/notifications#sending-notifications) for information on queues.

```php
use App\Notifications\OrderPaid;

$user->notify(new OrderPaid($order));
```
