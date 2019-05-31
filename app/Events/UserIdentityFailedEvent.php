<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Support\MessageBag;

class UserIdentityFailedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $user_id;
    protected $errors;
    
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user_id, MessageBag $errors)
    {
        $this->user_id = $user_id;
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
