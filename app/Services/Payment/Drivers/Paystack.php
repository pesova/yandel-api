<?php

namespace App\Services\Payment\Drivers;

use Auth;
use App\Contracts\PaymentDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use App\Exceptions\PaymentGatewayException;

class Paystack implements PaymentDriverInterface
{
    /**
     * Paystack Driver Configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * @var $http
     * @var string $logChannel
     */
    private $http, $logChannel = 'gateway';

    /**
     * Paystack Constructor
     */
    public function __construct( ?array $config )
    {
        $this->config = $config ?? $this->loadDefaultConfig();
        $this->http = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->config['private_key']
        ])->withOptions([
            'base_uri' => $this->config['base_url']
        ])->timeout( config('custom.app.API_TIMEOUT') );
    }

    /**
     * Retrieve default config.
     *
     * @return array
     */
    protected function loadDefaultConfig() : array
    {
        return config('custom.payment.drivers.paystack');
    }

    /*
    |--------------------------------------------------------------------------
    | CARD TOKENIZATION & MANAGEMENT
    |--------------------------------------------------------------------------
    */
    // confirm card can work for debit amount
    public function validateCard($authorization, $email, $amount) : array
    {
        $payload = [
            "authorization_code"=> $authorization, 
            "email"=> $email, 
            "amount"=> $amount * 100
        ];
        $req = $this->http->post('transaction/check_authorization', $payload);
        return $this->handleHttpRequest( $req );
    }

    public function chargeCard($reference, $email, $authorization, $amount, $meta = []) : array
    {
        $payload = [
            "authorization_code"=> $authorization, 
            "email"=> $email, 
            "amount"=> $amount * 100, 
            "reference"=>$reference,
            "metadata"=>json_encode($meta)
        ];
        $req = $this->http->post('transaction/charge_authorization', $payload);
        return $this->tranformResponse ( $this->handleHttpRequest( $req ) );
    }

    public function checkPendingCharge( string $reference ): array
    {
        $req = $this->http->get('charge/'.$reference);
        return $this->tranformResponse ( $this->handleHttpRequest( $req ) );
    }

    public function deactivateCard( string $authorization ): array
    {
        $payload = [ "authorization_code"=> $authorization ];
        $req = $this->http->post('customer/deactivate_authorization', $payload);
        return $this->handleHttpRequest( $req );
    }

    /*
    |--------------------------------------------------------------------------
    | BULK CHARGE
    |--------------------------------------------------------------------------
    */
    public function bulkCharge( array $payload ): array
    {
        $payload = [
            "authorization" => "AUTH_n95vpedf", 
	        "amount" => 2500
        ];
        $req = $this->http->post( 'bulkcharge', json_enncode($payload) );
        return $this->handleHttpRequest( $req );
    }

    public function getbulkChargeBatches(): array
    {
        $req = $this->http->get('bulkcharge');
        return $this->handleHttpRequest( $req );
    }

    public function getBulkChargeBatch($batchCode): array
    {
        $req = $this->http->get('bulkcharge/'.$batchCode);
        return $this->handleHttpRequest( $req );
    }

    public function pauseBulkChargeBatch($batchCode): array
    {
        $req = $this->http->get('bulkcharge/pause/'.$batchCode);
        return $this->handleHttpRequest( $req );
    }

    public function resumeBulkChargeBatch($batchCode): array
    {
        $req = $this->http->get('bulkcharge/resume/'.$batchCode);
        return $this->handleHttpRequest( $req );
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSACTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Checks the status of a transaction
     */
    public function verifyTransaction($reference): array
    {
        $req = $this->http->get('transaction/verify/'.$reference);
        return $this->tranformResponse ( $this->handleHttpRequest( $req ) );
    }

    public function listTransactions(
        $status='success', 
        $from='', 
        $to='', 
        $perPage=1000, 
        $page=1, 
        $amount='', 
        $customer='' ): array
    {
        $req = $this->http->get('transaction');
        return $this->handleHttpRequest( $req );
    }

    public function getTransactionTimeline($reference): array
    {
        $req = $this->http->get('transaction/timeline/'.$reference);
        return $this->handleHttpRequest( $req );
    }

    public function getTotalTransaction(): array
    {
        $req = $this->http->get('transaction/totals');
        return $this->handleHttpRequest( $req );
    }

    public function exportTransaction(): string
    {
        $req = $this->http->get('transaction/export');
        return $this->handleHttpRequest( $req );
    }

    /*
    |--------------------------------------------------------------------------
    | SETTLEMENT
    |--------------------------------------------------------------------------
    */

    public function fetchSettlements(): array
    {
        $req = $this->http->get('settlement');
        return $this->handleHttpRequest( $req );
    }


    /*
    |--------------------------------------------------------------------------
    | WEBHOOK
    |--------------------------------------------------------------------------
    */
    public function webhookHandler()
    {
        \Log::channel('gateway')->info($request->ip());

        \DB::beginTransaction();
        try{
            // push response data to the transaction reconciliation table
            $data = $request->data;
            $ip = $request->ip();
            $header = $request->header('x-paystack-signature') ?? null;
            if(!$header) {
                \Log::channel('gateway')->info([
                    'ERROR FROM webhook_handler'=>[
                        'HEADERS'=>$request->headers(),
                        'WEBHOOK'=>$request->all()
                    ]
                ]);
                return $this->success();
            }

            $user = \App\User::where('email', $data['customer']['email'])
                    ->join('goals', 'goals.user_id', 'users.id')
                    ->select('users.id', 'goals.id as goal_id')
                    ->first();

            // check IP address to confirm its either of

            if(!$user){
                \Log::channel('gateway')->info([
                    'ERROR FROM webhook_handler'=>[
                        'IP'=>$request->ip(),
                        $data['customer']['email'],
                        'WEBHOOK'=>$request->all()
                    ]
                ]);
                return $this->success();
            }
            // if( !in_array($request->ip(), ['52.31.139.75', '52.49.173.169', '52.214.14.220']) ) return $this->error();
            
            // confirm signature matches SHA512 of input and Secret key

            $rave_transaction = new \App\Rave_transaction();
            $rave_transaction->user_id = $user->id;	
            $rave_transaction->plan_type = 'goal';
            $rave_transaction->plan_id = $data['metadata']['goal_id'] ?? $user->goal_id;
            $rave_transaction->amount = $data['amount']/100;
            $rave_transaction->reference = $data['reference'];
            $rave_transaction->status = 'pending';
            $rave_transaction->response = 'Webhook data';
            $rave_transaction->retries = 0;
            $rave_transaction->save();

            \DB::commit();
        }
        catch(\Throwable $e){
            \DB::rollback();
            \Log::channel('gateway')->error([ 'ERROR FROM webhook_handler'=>$e->getMessage(), 'REQUEST'=>$request->all() ]);
        }
        // return a 200
        return $this->success();
    }

    /*
    |--------------------------------------------------------------------------
    | MISC
    |--------------------------------------------------------------------------
    */
    public function fetchBanks(): array
    {
        $req = $this->http->get('bank');
        return $this->handleHttpRequest( $req );
    }

    public function recipientEnquiry($nuban, $code): array
    {
        $req = $this->http->get("bank/resolve?account_number={$nuban}&bank_code={$code}");
        return $this->handleHttpRequest( $req );
    }
    

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    public function tranformResponse( array $response ): array
    {
        $data = $response['data'] ?? [];
        $chargecode = '-1';
        if(isset($data['status']) && $data['status'] == 'success') $chargecode = '0';
        
        $response = [
            'gateway'=>'paystack',
            'amount' => isset($data['amount']) ? $data['amount']/100 : 0,
            'fees' => isset($data['fees']) ? $data['fees']/100 : 0,
            'reference' => $data['reference'],
            'chargecode' =>  $chargecode,
            'status' => $data['status'] == 'successful' ? 'success' : $data['status'],
            'channel' => $data['channel'],
            'ip_address' => $data['ip_address'],
            'bank'=>isset($data['authorization']['bank']) ? $data['authorization']['bank'] : '',
            'card_type'=>isset($data['authorization']['card_type']) ? $data['authorization']['card_type'] : '',
            'last4'=>isset($data['authorization']['last4']) ? $data['authorization']['last4'] : '',
            'exp_year'=>isset($data['authorization']['exp_year']) ? $data['authorization']['exp_year'] : '',
            'exp_month'=>isset($data['authorization']['exp_month']) ? $data['authorization']['exp_month'] : '',
            'authorization_code'=>isset($data['authorization']['authorization_code']) ? $data['authorization']['authorization_code'] : '',
            'metadata' => $data['metadata'] ?? [],
            'gateway_response' => $data['gateway_response'] ?? ''
        ];

        return $response;
    }

    /**
     * Processes a given http request,
     * uniformely handing errors & success cases
     * 
     * TODO: make this a reusable trait
     *
     * @param Illuminate\Support\Facades\Http;
     * @return json
     * @throws exception
     */
    public function handleHttpRequest($req)
    {
        if(!$req->successful()) throw new PaymentGatewayException($req);

        return $req->json();
    }

    public function getPaymentLink( 
        string $reference, 
        string $email,
        float $amount, 
        string $currency = "NGN", 
        array $meta = null )
    {
        $meta = $meta ?? [];
        $meta['user_id'] = $meta['user_id'] ?? Auth::user()->id;
        $meta['cancel_action'] = $meta['cancel_action'] ?? $this->config['CANCEL_PAYMENT_URL'] ?? "https://pg/payment/cancel";
        $payload = [
            "email" => $email, 
            "amount" => $amount * 100, 
            "currency" => $currency, 
            "reference" => $reference,
            "channels" => ["card"],
            "metadata" => json_encode($meta),
            "callback_url" => $this->config["CALLBACK_PAYMENT_URL"] ?? "https://payment/verify"
        ];

        $req = $this->http->post('transaction/initialize', $payload);
        return $this->handleHttpRequest( $req );
    }
}