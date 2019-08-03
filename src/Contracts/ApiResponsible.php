<?php
/**
 * ApiResponsible Contract
 *
 * @author: tuanha
 * @last-mod: 29-July-2019
 */
namespace Bkstar123\ApiBuddy\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ApiResponsible
{
    /**
     * Send error response in JSON format
     *
     * @param  mixed  $errors
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($errors, int $status = 500) : \Illuminate\Http\JsonResponse;

    /**
     * Send success response in JSON format
     *
     * @param  mixed  $data
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data, int $status = 200) : \Illuminate\Http\JsonResponse;
    
    /**
     * Show a collection of resources
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  string $apiResource
     * @param  string $transformerClass
     * @return  mixed (JSON)
     */
    public function showCollection($builder, $apiResource = '', $transformerClass = '');

    /**
     * Show a resource instance
     *
     * @param  \Illuminate\Database\Eloquent\Model  $instance
     * @param  string $apiResource
     * @param  int $code
     * @return  mixed (JSON)
     */
    public function showInstance(Model $instance, $apiResource = '', $code = 200);
}
