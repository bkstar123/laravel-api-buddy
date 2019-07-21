<?php
/**
 * ApiResponser.php
 *
 * @author: tuanha
 * @last-mod: 11-July-2019
 */
namespace Bkstar123\ApiBuddy\Services;

use Illuminate\Database\Eloquent\Model;
use Bkstar123\ApiBuddy\Contracts\ApiResponsible;
use Bkstar123\ApiBuddy\Contracts\ResourceCollectionProcessable;

class ApiResponser implements ApiResponsible
{
    /**
     * @var \Bkstar123\ApiBuddy\Contracts\ResourceCollectionProcessable
     */
    protected $processor;

    /**
     * @param  \Bkstar123\ApiBuddy\Contracts\ApiResponseProcessor  $processor
     * @return void
     */
    public function __construct(ResourceCollectionProcessable $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @return \Illuminate\Http\JsonResponse
     */
    public function showCollection($builder) : \Illuminate\Http\JsonResponse
    {
        return $this->successResponse($this->processor->processCollection($builder));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model  $instance
     * @return \Illuminate\Http\JsonResponse
     */
    public function showInstance(Model $instance) : \Illuminate\Http\JsonResponse
    {
        return $this->successResponse($this->processor->processInstance($instance));
    }

    /**
     * @param  mixed  $errors
     * @param  int  $status  
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($errors, int $status = 500) : \Illuminate\Http\JsonResponse
    {
        return response()->json(['errors' => $errors, 'code' => $status], $status);
    }

    /**
     * @param  mixed  $data
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data, int $status = 200) : \Illuminate\Http\JsonResponse
    {
        if (is_array($data) && array_key_exists('data', $data)) {
            return response()->json($data, $status);
        }

        return response()->json(['data' => $data], $status);
    }
}
