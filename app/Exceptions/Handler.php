<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Sentry\State\Scope;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\QueryException;
use \Laravel\Passport\Exceptions\OAuthServerException;
use \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use \Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// use \Illuminate\Auth\AuthenticationException;
// use \Illuminate\Auth\Access\AuthorizationException;
use \Symfony\Component\HttpKernel\Exception\HttpException;
// use \Illuminate\Session\TokenMismatchException;
// use \Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            if (auth()->check()) {
                \Sentry\configureScope(function (Scope $scope): void {
                    $scope->setUser([
                        'id' => auth()->user()->id,
                        'email' => auth()->user()->email,
                    ]);
                });
            }
            app('sentry')->captureException($exception);
        }

        parent::report($exception);

        if($exception instanceof MassAssignmentException){
            \Log::channel('hack')->error(["Attempt to fill non-fillable property"=>$exception->getMessage()]);
        }
        if($exception instanceof QueryException){
            \Log::channel('db')->error(["Query Exception"=>$exception->getMessage()]);
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if(app()->environment() === 'local') return parent::render($request, $exception);
        
        if ($exception instanceof ConnectionException){
            return error($exception->getMessage(), 500);
        }
        if ($exception instanceof RequestException){
            return badRequest($exception->getMessage(), 400);
        }
        // TODO: try to inlcude the name of model
        if($exception instanceof ModelNotFoundException){
            $modelName = explode( "\\", $exception->getModel() );
            return notFound(" Requested " . end( $modelName ) ." was not found");
        }
        if($exception instanceof MassAssignmentException){
            return unAuthorized('You are trying to fill an unfillable property. I am watching you!');
        }
        if($exception instanceof UnauthorizedException){
            return unAuthorized($exception->getMessage());
        }
        if($exception instanceof QueryException){
            return error('A query exception occured', 500);
        }
        if($exception instanceof OAuthServerException){
            return error('Invalid login credential', 401);
        }
        if($exception instanceof MethodNotAllowedHttpException){
            return error('Http method used is not valid for requested resource', 404);
        }
        if($exception instanceof NotFoundHttpException){
            return error('Not found', 404);
        }
        if($exception instanceof \InvalidArgumentException){
            return error($exception->getMessage(), 400);
        }
        if($exception instanceof \TypeError){
            return error($exception->getMessage(), 400);
        }
        if($exception instanceof \Error){
            return error($exception->getMessage(), 500);
        }
        if($exception instanceof \ErrorException){
            return error($exception->getMessage(), 500);
        }
        if($exception instanceof \UnexpectedValueException){
            return error($exception->getMessage(), 500);
        }

        if($exception instanceof \Symfony\Component\ErrorHandler\Error\FatalError){
            return error($exception->getMessage(), 400);
        }

        return parent::render($request, $exception);
    }
}
