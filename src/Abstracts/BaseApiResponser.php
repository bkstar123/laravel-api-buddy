<?php
/**
 * BaseApiResponser abstract
 *
 * @author: tuanha
 * @last-mod: 29-July-2019
 */
namespace Bkstar123\ApiBuddy\Abstracts;

use Illuminate\Database\Eloquent\Model;
use Bkstar123\ApiBuddy\Contracts\ApiResponsible;
use Bkstar123\ApiBuddy\Contracts\ResourceCollectionProcessable;

abstract class BaseApiResponser implements ApiResponsible
{
    /**
     * @var \Bkstar123\ApiBuddy\Contracts\ResourceCollectionProcessable
     */
    protected $processor;

    /**
     * @param  \Bkstar123\ApiBuddy\Contracts\ResourceCollectionProcessable  $processor
     * @return void
     */
    public function __construct(ResourceCollectionProcessable $processor)
    {
        $this->processor = $processor;
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

    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  string $apiResource
     * @param  string $transformerClass
     * @return  mixed (JSON)
     */
    abstract public function showCollection($builder, $apiResource = '', $transformerClass = '');

    /**
     * @param \Illuminate\Database\Eloquent\Model  $instance
     * @param  string $apiResource
     * @param  int $code
     * @return  mixed (JSON)
     */
    abstract public function showInstance(Model $instance, $apiResource = '', $code = 200);
}
