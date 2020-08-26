<?php

namespace App\Mail;

use App\Models\Database\User;
use App\Models\Database\UserExt;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Support\MessageBag;

class IdentityDuplicate extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $duplicate1;
    protected $duplicate2;
    protected $errors;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(UserExt $user, User $duplicate1, User $duplicate2, MessageBag $errors)
    {
        $this->user = $user;
        $this->duplicate1 = $duplicate1;
        $this->duplicate2 = $duplicate2;
        $this->errors = $errors;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->subject(__('Identity duplicate problem'))
        ->text('identityduplicatemail');
    }
}
