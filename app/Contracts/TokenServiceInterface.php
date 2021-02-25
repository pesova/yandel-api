<?php

namespace App\Contracts;

interface TokenServiceInterface 
{
    /**
     * Generates a token
     * same token cant be generated twice for same channel and event
     * 
     * @param string $channel
     * @param string $subscriber
     * @param string $event
     * @param string $expiresInSeconds
     * 
     * @return string
     */
    public function generateToken(
        string $channel, 
        string $subscriber, 
        string $event, 
        int $expiresInSeconds
    ):string;
    
    /**
     * Checks that a given token is valid
     * 
     * @param string $channel
     * @param string $subscriber
     * @param string $event
     * @param string $token
     * 
     * @return bool
     */
    public function verifyToken(
        string $channel, 
        string $subscriber, 
        string $event,
        string $token
    ):bool;
}