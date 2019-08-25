<?php
/**
 * ApiResponsible Contract
 *
 * @author: tuanha
 * @last-mod: 29-July-2019
 */
namespace Bkstar123\ApiBuddy\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

interface ApiResponsible
{
    /**
     * Send error response in JSON format
     *
     * @param  mixed  $errors
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($errors, int $status = 500) : JsonResponse;

    /**
     * Send success response in JSON format
     *
     * @param  mixed  $data
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data, int $status = 200) : JsonResponse;
    
    /**
     * Show a collection of resources
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  string $apiResource
     * @param  string $transformerClass
     * @return \Illuminate\Http\JsonResponse
     */
    public function showCollection(Builder $builder, 
        string $apiResource = '', 
        string $transformerClass = '') : JsonResponse;

    /**
     * Show a resource instance
     *
     * @param  \Illuminate\Database\Eloquent\Model  $instance
     * @param  string $apiResource
     * @param  string $transformerClass
     * @param  int $code
     * @return  \Illuminate\Http\JsonResponse
     */
    public function showInstance(Model $instance, 
        string $apiResource = '', 
        string $transformerClass = '', 
        int $code = 200) : JsonResponse;
}
