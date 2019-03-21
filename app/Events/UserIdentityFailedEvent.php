<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Database\UserExt;
use Illuminate\Contracts\Support\MessageBag;

class UserIdentityFailedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $user;
    protected $errors;
    
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(UserExt $user, MessageBag $errors)
    {
        $this->user = $user;
        $this->errors = $errors;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
