<?php
/**
 * ApiResponser Service
 *
 * @author: tuanha
 * @last-mod: 11-July-2019
 */
namespace Bkstar123\ApiBuddy\Services;

use Illuminate\Database\Eloquent\Model;
use Bkstar123\ApiBuddy\Abstracts\BaseApiResponser;
use Bkstar123\ApiBuddy\Http\Resources\AppResource;

class ApiResponser extends BaseApiResponser
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  string $apiResource
     * @param  string $transformerClass
     * @return  mixed (JSON)
     */
    public function showCollection($builder, $apiResource = '', $transformerClass = '')
    {
        if (config('bkstar123_apibuddy.useTransform') && !empty($apiResource) && !empty($transformerClass)) {
            return $apiResource::collection($this->processor->processCollection($builder, $transformerClass));
        } else {
            return $this->successResponse($this->processor->processCollection($builder)->toArray());
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model  $instance
     * @param  string $apiResource
     * @return  mixed (JSON)
     */
    public function showInstance(Model $instance, $apiResource = '')
    {
        if (config('bkstar123_apibuddy.useTransform')) {
            if (is_subclass_of($apiResource, AppResource::class)) {
                return  new $apiResource($this->processor->processInstance($instance));
            }
            throw new \Exception('The second argument passed to showInstance() method of the class ' 
                      . get_class(). ' must be a sub-class of '. AppResource::class);
        }
        return $this->successResponse($this->processor->processInstance($instance));
    }
}
