<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PasswordResetRequest
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var mixed $reset
     */
    public $reset;

    /**
     * @var \App\Models\User $user
     */
    public $user;

    /**
     * Create a new event instance.
     * 
     * @param mixed $event
     * @param \App\Models\User $user = null
     *
     * @return void
     */
    public function __construct( $reset, \App\Models\User $user = null )
    {
        $this->reset = $reset;
        $this->user = $user ?? auth()->user();
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
