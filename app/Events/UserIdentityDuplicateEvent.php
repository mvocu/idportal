<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Support\MessageBag;

class UserIdentityDuplicateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    protected $user_id;
    protected $duplicate1_id;
    protected $duplicate2_id;
    protected $errors;
    
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user_id, $duplicate1_id, $duplicate2_id, MessageBag $errors)
    {
        $this->user_id = $user_id;
        $this->duplicate1_id = $duplicate1_id;
        $this->duplicate2_id = $duplicate2_id;
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

