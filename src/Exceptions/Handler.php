<?php
/**
 * Exception Handler
 *
 * @author: tuanha
 * @last-mod: 21-04-2019
 */
namespace Bkstar123\ApiBuddy\Exceptions;

use Exception;
use Asm89\Stack\CorsService;
use Bkstar123\ApiBuddy\Traits\ApiResponser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Bkstar123\ApiBuddy\Exceptions\ApiExceptionHandler;
use Bkstar123\ApiBuddy\Exceptions\WebExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    use ApiResponser, ApiExceptionHandler, WebExceptionHandler;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
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
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $response = $this->handleException($request, $exception);

        app(CorsService::class)->addActualRequestHeaders($response, $request);

        return $response;
    }

    /**
     * Handle various types of exception
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    protected function handleException($request, Exception $exception)
    {
        if (is_null($request->route()) || $this->isWebAccess($request)) {
            return $this->webHandleException();
        }

        return $this->apiHandleException($request, $exception);
    }

    /**
     * Create a response object from the given validation exception.
     *
     * @param  \Illuminate\Validation\ValidationException  $e
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $exception, $request)
    {
        $errors = $exception->validator->errors()->getMessages();

        if ($this->isWebAccess($request)) {
            return $request->expectsJson() ?
                   $this->errorResponse($errors, 422) :
                   redirect()->back()
                             ->withInput($request->except($this->dontFlash))
                             ->withErrors($errors);
        }

        return $this->errorResponse($errors, 422);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($this->isWebAccess($request)) {
            return $request->expectsJson() ?
                   $this->errorResponse($exception->getMessage(), 401) :
                   redirect()->guest($exception->redirectTo() ?? route('login'));
        }

        return $this->errorResponse($exception->getMessage(), 401);
    }
    
    /**
     * Determine whether a request is via Web or API call
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isWebAccess($request) : bool
    {
        return $request->acceptsHtml() && collect($request->route()->middleware())->contains('web');
    }
}
