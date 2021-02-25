<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TokenGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $channel;
    public $subscriber;
    public $event;
    public $token;
    /**
     * @var null
     */
    public $message;
    /**
     * @var null
     */
    public $user;
    public $username;

    /**
     * Create a new event instance.
     *
     * @param $channel
     * @param $subscriber
     * @param $event
     * @param $token
     * @param $username
     * @param null $message
     */
    public function __construct($channel, $subscriber, $event, $token, $username , $message = null)
    {
        //
        $this->channel = $channel;
        $this->subscriber = $subscriber;
        $this->event = $event;
        $this->token = $token;
        $this->message = $message;
        $this->username = $username;
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
