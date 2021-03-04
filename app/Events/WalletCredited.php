<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletCredited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var mixed $wallet
     */
    public $wallet;

    /**
     * @var float $amount
     */
    public $amount;

    /**
     * @var \App\Models\User $user
     */
    public $user;

    /**
     * Create a new event instance.
     * 
     * @param mixed $wallet
     * @param \App\Models\User $user = null
     *
     * @return void
     */
    public function __construct( $wallet, float $amount, \App\Models\User $user = null )
    {
        $this->wallet = $wallet;
        $this->amount = $amount;
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
