<?php
/**
 * WebExceptionHandler Trait
 *
 * Handle exceptions for Web routes
 * @author: tuanha
 * @last-mod: 20-July-2019
 */
namespace Bkstar123\ApiBuddy\Exceptions;

use Exception;

trait WebExceptionHandler
{
    /**
     * webHandleException
     *
     * Handle exceptions for Web routes
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     */
    protected function webHandleException($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }
}
