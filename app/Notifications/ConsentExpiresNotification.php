<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class ConsentExpiresNotification extends Notification
{
    use Queueable;
    
    private $user;
    private $token;
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
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
        ->subject(Lang::getFromJson('Uvaly - account consent expiration'))
        ->line(Lang::getFromJson('Your consent with terms of usage will expire soon and needs to be renewed.') . " " .
            Lang::getFromJson('Please renew your consent by visiting the link below.'))
        ->action(Lang::getFromJson('Renew Consent'), route('consent.ask')))
            ->line(Lang::getFromJson('If you do not renew your consent, your account will be disabled.'));
    }
    
}
