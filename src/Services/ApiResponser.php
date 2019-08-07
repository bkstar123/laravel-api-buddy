<?php
/**
 * ApiResponser Service
 *
 * @author: tuanha
 * @last-mod: 11-July-2019
 */
namespace Bkstar123\ApiBuddy\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Bkstar123\ApiBuddy\Abstracts\BaseApiResponser;
use Bkstar123\ApiBuddy\Http\Resources\AppResource;
use Bkstar123\ApiBuddy\Transformers\AppTransformer;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class ApiResponser extends BaseApiResponser
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  string $apiResource
     * @param  string $transformerClass
     * @return  \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function showCollection($builder, $apiResource = '', $transformerClass = '')
    {
        if (!($builder instanceof QueryBuilder) && !($builder instanceof EloquentBuilder)) {
            throw new Exception('The first argument passed to the showCollection() method of the class '
                  . get_class(). ' must be an instance of either '. QueryBuilder::class. ' or '. EloquentBuilder::class);
        }

        if (config('bkstar123_apibuddy.useTransform')) {
            if (!is_subclass_of($apiResource, AppResource::class)) {
                throw new Exception('The second argument passed to the showCollection() method of the class '
                      . get_class(). ' must be a sub-class of '. AppResource::class);
            }

            if (!is_subclass_of($transformerClass, AppTransformer::class)) {
                throw new Exception('The third argument passed to the showCollection() method of the class '
                      . get_class(). ' must be a sub-class of '. AppTransformer::class);
            }
            return $apiResource::collection($this->processor->processCollection($builder, $transformerClass));
        } else {
            return $this->successResponse($this->processor->processCollection($builder)->toArray());
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model  $instance
     * @param  string $apiResource
     * @param  int $code
     * @return  \Illuminate\Http\JsonResponse
     */
    public function showInstance(Model $instance, $apiResource = '', $code = 200) : \Illuminate\Http\JsonResponse
    {
        if (config('bkstar123_apibuddy.useTransform')) {
            if (!is_subclass_of($apiResource, AppResource::class)) {
                throw new Exception('The second argument passed to the showInstance() method of the class '
                          . get_class(). ' must be a sub-class of '. AppResource::class);
            }
            return $this->successResponse(new $apiResource($this->processor->processInstance($instance)), $code);
        }
        return $this->successResponse($this->processor->processInstance($instance));
    }
}
