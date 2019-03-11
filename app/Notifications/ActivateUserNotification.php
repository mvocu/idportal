<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class ActivateUserNotification extends Notification
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
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
        ->subject(Lang::getFromJson('Account Activation Required'))
        ->line(Lang::getFromJson('You are receiving this email because we received a new account registration request.'))
        ->action(Lang::getFromJson('Activate Account'), url(config('app.url').route('auth.activate', $this->token, false)))
        ->line(Lang::getFromJson('If you did not register new account, no further action is required.'));
    }
    
}

