<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\User;

class LoginSuccess
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var $user
     * @var $device
     */
    public $user, $device;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $device = null)
    {
        $this->user = $user;
        $this->device = $device;
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
