<?php
/**
 * ApiExceptionHandler.php
 *
 * Handle exceptions for API routes
 * @author: tuanha
 * @last-mod: 20-July-2019
 */
namespace Bkstar123\ApiBuddy\Exceptions;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

trait ApiExceptionHandler
{
	/**
     * apiHandleException
     *
     * Handle exceptions for API routes
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     */
    private function apiHandleException($request, Exception $exception)
    {
        if ($exception instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        if ($exception instanceof ModelNotFoundException) {
            $modelName = strtolower(class_basename($exception->getModel()));
            return $this->errorResponse("Does not exist any {$modelName} with the specified identificator", 404);
        }

        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof AuthorizationException) {
            return $this->errorResponse($exception->getMessage(), 403);
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->errorResponse('The specified url cannot be found', 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->errorResponse('The specified method for the request is invalid', 405);
        }

        if ($exception instanceof HttpException) {
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }

        if ($exception instanceof QueryException) {
            $errorCode = $exception->errorInfo[1];
            if ($errorCode == 1451) {
                return $this->errorResponse('Cannot remove this resource permanently, it is related with an another resource', 409);
            } else if ($errorCode == 1062) {
                return $this->errorResponse('Cannot create this resource as it is duplicated with an existing one', 409);
            }
            return $this->errorResponse('Failed to proceed this request due to an unknown reason', 400);
        }

        if (config('app.debug')) {
            return parent::render($request, $exception);
        }
        return $this->errorResponse('Unexpected exception. Please try later', 500);
    }
}