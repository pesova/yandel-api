<?php

namespace App\Exceptions;

use Exception;

class AuthServiceException extends Exception
{
    /**
     * @var array
     * @var array
     * @var Exception
     * @var string
     * @var int
     */
    protected $message, $request, $exception, $response = '', $code;

    public function __construct(string $message, array $request = null, $exception = null, int $code = 401){
        $this->message = $message ?? '';
        $this->request = $request;
        $this->exception = $exception;
        $this->code = $code;
        if($this->exception instanceof \Throwable) {
            $this->message.= ' : '.$exception->getMessage()
            .' in '.$exception->getFile()
            .' on line '.$exception->getLine();

            $this->response = $message.= ' : '.$exception->getMessage();
        }
    }

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        \Log::channel('auth')->error( [
            'Response'=>$this->message,
            'Request'=>$this->request ?? null
        ] );

        return false;
    }

    
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        // TODO: check for exceptions that should not be reported
        // e.g. DB exceptionns
        return error($this->message, $this->code);
    }
}
