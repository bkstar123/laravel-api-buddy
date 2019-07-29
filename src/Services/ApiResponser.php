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

class ApiResponser extends BaseApiResponser
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  string $apiResource
     * @param  string $modelClass
     * @return  mixed (JSON)
     */
    public function showCollection($builder = null, $apiResource = '', $modelClass = '')
    {
        if (config('bkstar123_apibuddy.useTransform') && !empty($apiResource) && !empty($modelClass)) {
            $transformerClass =  $modelClass::$transformer;
            if ($builder === null) {
                $builder = $modelClass::query();
            }
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
        if (config('bkstar123_apibuddy.useTransform') && !empty($apiResource)) {
            return  new $apiResource($this->processor->processInstance($instance));
        }
        return $this->successResponse($this->processor->processInstance($instance));
    }
}
