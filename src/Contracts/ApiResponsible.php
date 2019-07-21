<?php
/**
 * ApiResponsible.php
 *
 * @author: tuanha
 * @last-mod: 21-July-2019
 */
namespace Bkstar123\ApiBuddy\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ApiResponsible
{
	/**
     * Show a collection of resources
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @return \Illuminate\Http\JsonResponse
     */
    public function showCollection($builder) : \Illuminate\Http\JsonResponse;

    /**
     * Show a resource instance
     *
     * @param \Illuminate\Database\Eloquent\Model  $instance
     * @return \Illuminate\Http\JsonResponse
     */
    public function showInstance(Model $instance) : \Illuminate\Http\JsonResponse;

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
}