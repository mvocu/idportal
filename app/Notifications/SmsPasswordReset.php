<?php

namespace App\Notifications;

use App\Notifications\Channels\AwegSmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Messages\SmsMessage;

class SmsPasswordReset extends Notification
{
    use Queueable;

    private $token;
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [ AwegSmsChannel::class ];
    }


    public function toSms($notifiable) {
        return new SmsMessage(__('sms-reset-code', [ 'token' => $this->token ]));
    }
    
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
