<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Database\User;
use App\Models\Database\ExtSource;

class AccountRequested extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $source;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, ExtSource $source)
    {
        $this->user = $user;
        $this->source = $source;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(__('New account requested'))
            ->text('userextmail');
    }
}
