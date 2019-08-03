<?php
/**
 * ResourceCollectionProcessor Abstract
 *
 * @author: tuanha
 * @last-mod: 21-July-2019
 */
namespace Bkstar123\ApiBuddy\Abstracts;

use Illuminate\Database\Eloquent\Model;
use Bkstar123\ApiBuddy\Contracts\ResourceCollectionProcessable;

abstract class ResourceCollectionProcessor implements ResourceCollectionProcessable
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  string  $transformerClass
     * @return  \Illuminate\Pagination\LengthAwarePaginator
     */
    public function processCollection($builder, $transformerClass = '') : \Illuminate\Pagination\LengthAwarePaginator
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
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  string $transformerClass
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    abstract public function filterData($builder, $transformerClass = '');

    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  string $transformerClass
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    abstract public function sortData($builder, $transformerClass = '');

    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    abstract public function selectFields($builder);

    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    abstract public function paginateData($builder) : \Illuminate\Pagination\LengthAwarePaginator;
}
