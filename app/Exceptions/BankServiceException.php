<?php

namespace App\Exceptions;

use Exception;

class BankServiceException extends Exception
{
    protected $error, $code;

    /**
    * Exception constructor.
    * @param $error
    */
    public function __construct($error = null, $code = 400)
    {
        $this->error = $error ?? 'Something went wrong. Please retry.';
        $this->code = $code;
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
         * Endpoint called, status code, response
         */
        \Log::channel('bank')->error($this->error);
        
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
        return error($this->error, $this->code);
    }
}
