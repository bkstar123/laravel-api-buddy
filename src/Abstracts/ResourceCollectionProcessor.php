<?php
/**
 * ResourceCollectionProcessor Abstract
 *
 * @author: tuanha
 * @last-mod: 21-July-2019
 */
namespace Bkstar123\ApiBuddy\Abstracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Bkstar123\ApiBuddy\Contracts\ResourceCollectionProcessable;

abstract class ResourceCollectionProcessor implements ResourceCollectionProcessable
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  string  $transformerClass
     * @return  \Illuminate\Pagination\LengthAwarePaginator
     */
    public function processCollection(Builder $builder, string $transformerClass = '') : \Illuminate\Pagination\LengthAwarePaginator
    {
        $builder =  $this->filterData($builder, $transformerClass);
        $builder =  $this->sortData($builder, $transformerClass);
        $builder = $this->selectFields($builder);
        return $this->paginateData($builder);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $instance
     * @return  mixed
     */
    public function processInstance(Model $instance)
    {
        if (config('bkstar123_apibuddy.useTransform')) {
            return $instance;
        }
        $builder = $instance->getQuery();
        return $this->selectFields($builder)->where('id', $instance->id)->first();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  string $transformerClass
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract public function filterData(Builder $builder, string $transformerClass = '');

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  string $transformerClass
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract public function sortData(Builder $builder, string $transformerClass = '');

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract public function selectFields(Builder $builder);

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    abstract public function paginateData(Builder $builder) : \Illuminate\Pagination\LengthAwarePaginator;
}
