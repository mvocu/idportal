<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class MailVotingCodeNotification extends Notification
{
    use Queueable;
    
    private $user;
    private $token;
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $token)
    {
        $this->user = $user;
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
        ->subject(Lang::getFromJson('Voting token'))
        ->line(Lang::getFromJson('You have been assigned the following voting code: :token', [ 'token' => $this->token ]))
        ->line(Lang::getFromJson('You can use this code for voting in participative budget.'))
        ->action(Lang::getFromJson('Vote here'), url('https://mojeobec.kr-stredocesky.cz/portal/paroz/uvaly/'));
    }
    
}

