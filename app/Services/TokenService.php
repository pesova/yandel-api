<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Token;
use App\Events\TokenGenerated;
use App\Exceptions\AuthServiceException;
use App\Contracts\TokenServiceInterface;
use App\Exceptions\TokenServiceException;

/**
 * Inherits basic CRUD functionalities from BaseService
 */
class TokenService extends BaseService implements TokenServiceInterface
{
    /**
     * Inject Dependencies
     */
    public function __construct(Token $token)
    {
        $this->model = $token;
    }

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
        int $expiresInSeconds=null,
        string $message = null
    ):string
    {
        $expiresInSeconds = $expiresInSeconds ?? config('custom.app.TOKENS_EXPIRES_IN');
        $token = random_strings(6);
        $expires_at = Carbon::now()->addSeconds($expiresInSeconds);

        $query = $this->model->updateOrCreate(
            [
                'channel' => $channel,
                'subscriber' => $subscriber,
                'event' => $event,
            ],
            [
                'token' => $token,
                'verified'=>false,
                'expires_at' => $expires_at
            ]
        );

        $user = User::where($channel, $subscriber)->first();
        $name = $user ? $user->name : $subscriber;

        event(new TokenGenerated($channel, $subscriber, $event, $token, $name));

        return $query->token;
    }

    /**
     * Checks that a given token is valid
     *
     * @param string $channel
     * @param string $subscriber
     * @param string $event
     * @param string|null $token
     *
     * @return bool
     * @throws TokenServiceException
     */
    public function verifyToken(
        string $channel,
        string $subscriber,
        string $event,
        string $token = null
    ):bool
    {
        $validToken = $this->model->where([
            'channel' => $channel,
            'subscriber' => $subscriber,
            'event' => $event
        ] )
        ->when($token, function($query, $token){
            $query->where('token', $token);
        })
        ->first();

        // if a token is supplied, validate the token
        if( isset($token) ){
            if( !$validToken ) throw new TokenServiceException('Supplied token does not exist.');
            if( $validToken->expires_at < Carbon::now() ) throw new TokenServiceException('Supplied token is expired.');
            if( $validToken->verified ) throw new TokenServiceException('Supplied token has already been verified.');

            $validToken->verified = true;
            $validToken->save();
        }else{
            if( !$validToken ) throw new TokenServiceException(' No token found for specified subscriber ');
        }

        // return the status of a found token
        return ($validToken->verified || !$validToken) ? false : true;
    }
}
