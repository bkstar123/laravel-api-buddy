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
    protected function apiHandleException($request, Exception $exception)
    {
        switch (true) {
            case $exception instanceof ValidationException:
                return $this->convertValidationExceptionToResponse($exception, $request);
                break;
            case $exception instanceof AuthenticationException:
                return $this->unauthenticated($request, $exception);
                break;
            case $exception instanceof ModelNotFoundException:
                $modelName = strtolower(class_basename($exception->getModel()));
                return $this->apiResponser->errorResponse("There is no {$modelName} of the given identificator", 404);
                break;
            case $exception instanceof AuthorizationException:
                return $this->apiResponser->errorResponse($exception->getMessage(), 403);
                break;
            case $exception instanceof NotFoundHttpException:
                return $this->apiResponser->errorResponse('URL not found', 404);
                break;
            case $exception instanceof MethodNotAllowedHttpException:
                return $this->apiResponser->errorResponse('Invalid request method', 405);
                break;
            case $exception instanceof HttpException:
                return $this->apiResponser->errorResponse($exception->getMessage(), $exception->getStatusCode());
                break;
            case $exception instanceof QueryException:
                $errorCode = $exception->errorInfo[1];
                if ($errorCode == 1451) {
                    return $this->apiResponser->errorResponse('The resource cannot be removed due to it is being referenced by others', 409);
                } else if ($errorCode == 1062) {
                    return $this->apiResponser->errorResponse('The resource already exists', 409);
                }
                return $this->apiResponser->errorResponse('Failed to proceed this request due to an unknown reason', 400);
                break;
            default:
                if (config('app.debug')) {
                    return parent::render($request, $exception);
                }
                return $this->apiResponser->errorResponse('Unexpected exception. Please try later', 500);
                break;
        } 
    }
}