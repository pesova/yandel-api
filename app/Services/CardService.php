<?php

namespace App\Services;

use Auth;
use Carbon\Carbon;
use App\Models\Card;
use App\Models\User;
use App\Events\CardExpired;
use App\Facades\PaymentGateway;
use App\Contracts\CardServiceInterface;
use App\Contracts\PaymentGatewayInterface;

/**
 * Inherits basic CRUD functionalities from BaseService
 */
class CardService extends BaseService implements CardServiceInterface
{
    /**
     * @var $card
     */
    private $card, $gateway;

    /**
     * Constructing the Card service with Card model
     *
     * @param App\Models\Card $card
     */
    public function __construct( Card $card, PaymentGatewayInterface $gateway )
    {
        $this->model = $card;
        $this->card = $card;
        $this->gateway = $gateway;
    }

    /**
     * Initializes charge using inline modal
     *
     * @param float $amount,
     * @param string $reference,
     * @param string $gateway = null
     *
     * @return string
     */
    public function initInlineCharge( ?float $amount, string $reference, ?string $gateway = null )
    {
        if($gateway) $this->gateway->use($gateway);
        return $this->gateway->getPaymentLink(
                    $reference,
                    auth()->user()->email,
                    $amount,
                    "NGN"
                );
    }

    public function debit( Card $params, float $amount, string $reference )
    {
        $email = $params['email'] ?? auth()->user()->email;

        // TODO: validate the card first, when live uncomment the below line
        // if(! $this->gateway->validateCard($params['token'], $email, $amount) )
        //     throw new CardServiceException(INSUFFICIENT_BALANCE);

        $charge = $this->gateway->chargeCard(
            $reference,
            $email,
            $params['token'],
            $amount,
            $params['meta'] ?? ['user_id' => auth()->user()->id] // TODO: add info to identify what user is crediting
        );

        return $charge['data'] ?? $charge;
    }

    public function saveCard(array $params){
        return $this->card->withTrashed()->updateOrCreate(
            [
                'user_id'=>$params['metadata']['user_id'] ?? auth()->user()->id,
                'gateway'=>$params['gateway'],
                'bank'=>$params['bank'],
                'type'=>$params['card_type'],
                'last4'=>$params['last4'],
                'expiration_year'=>$params['exp_year'],
                'expiration_month'=>$params['exp_month'],
            ],
            [
                'token' => $params['authorization_code'],
                'deleted_at' => null
            ]
        );

    }

    /**
     * Delete an existing card
     *
     * @param int $card_id
     *
     * @return array|null
     */
    public function deleteCard(int $authorization_id)
    {
        // delete internally
        $deleteCard = auth()->user()->cards()->findOrFail($authorization_id);

        // disable on gateway as well
        try{
            $this->gateway->use($deleteCard->gateway)->deactivateCard( $deleteCard->token );
        }catch(\Throwable $e){
            handleThrowable($e, 'gateway', 'deleteCard', $deleteCard->toArray());
        }

        return $deleteCard->delete();
    }

    /**
     * checks if a user has a bank account
     *
     * @param bool $return - determines if model or boolean is returned
     *
     * @return bool|array
     */
    public function hasCard( bool $return = false,$user = null)
    {
        $user = $user ?? Auth::user();

        $hasCard = $this->model->where('user_id', $user->id);
        if( $return ) return $hasCard->first();

        return $hasCard->exists();
    }

    /**
     * checks if a specified card belongs to specified user
     *
     * @return bool
     */
    public function belongsToUser( int $card_id, $user = null)
    {
        $user = $user ?? Auth::user();
        return $this->model
                ->where(['user_id'=>$user->id, 'id'=>$card_id])
                ->exists();
    }

    /**
     * returns a list of cards belonging to a user
     *
     * @return null|object
     */
    public function listCards()
    {
        return $this->model
                ->where( 'user_id', Auth::user()->id)
                ->orderBy('updated_at', 'DESC')
                ->get();
    }

    /**
     * finds a card by a combination of its properties
     * helps to avoid duplicate card storage
     *
     * @param array $cardProperties
     * @param bool $withTrashed
     *
     * @return bool
     */
    public function findByProperties( array $cardProperties, bool $withTrashed = false ): ?Card
    {
        return $this->model->when( $withTrashed, function ($query){
                    $query->withTrashed();
                })
                ->where('user_id', Auth::user()->id)
                ->where('bank', $cardProperties['bank'])
                ->where('type', $cardProperties['type'])
                ->where('last4', $cardProperties['last4'])
                ->where('expiration_year', $cardProperties['expiration_year'])
                ->where('expiration_month', $cardProperties['expiration_month'])
                ->first();
    }

    /**
     * Dynamically proxy method calls to the payment driver.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->gateway->{$method}(...$parameters);
    }
}
