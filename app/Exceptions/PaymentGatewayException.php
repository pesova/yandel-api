<?php

namespace App\Exceptions;

use Exception;

class PaymentGatewayException extends Exception
{
    
    protected $error, $code;

    /**
    * Exception constructor.
    * @param $error
    */
    public function __construct($error, $code = 400)
    {
        $this->error = $error;
        $this->code = $code;
        
        parent::__construct($this->error, $this->code);
    }

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        /**
         * Log error message
         * 
         * TODO: Error should be an array, having 
         * Endpoint called, status code, response
         */
        \Log::channel('cba')->error( $this->error );

        return false;
    }

    
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     * 
     * @TODO: build a responsable trait for basically 
     * returning http responses
     */
    public function render($request)
    {
        $error = $this->error->json();
        $errorCode = $error ? $error['status'] ?? $this->error->status() : null;
        
        if(isset($error) && isset($error['message']) ){
            $message = $error['message'];
        }elseif($this->error->status() === 404){
            $message = "You may have called an invalid resource";
        }else{
            $message = 'Call to CbaService failed';
        }

        return error(($message), $this->error->status(), (int) $errorCode);
    }
}
