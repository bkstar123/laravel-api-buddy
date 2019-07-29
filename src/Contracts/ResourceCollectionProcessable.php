<?php
/**
 * ResourceCollectionProcessable Contract
 *
 * @author: tuanha
 * @last-mod: 29-July-2019
 */
namespace Bkstar123\ApiBuddy\Contracts;

interface ResourceCollectionProcessable
{
    /**
     * Get resource collection and process it with sorting, filtering, selecting and paginating
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  string  $transformerClass
     * @return  \Illuminate\Pagination\LengthAwarePaginator
     */
    public function processCollection($builder, $transformerClass = '') : \Illuminate\Pagination\LengthAwarePaginator;

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $instance
     * @return  mixed
     */
    public function processInstance($instance);
}
