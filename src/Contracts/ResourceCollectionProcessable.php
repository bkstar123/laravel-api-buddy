<?php
/**
 * ResourceCollectionProcessable Contract
 *
 * @author: tuanha
 * @last-mod: 29-July-2019
 */
namespace Bkstar123\ApiBuddy\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

interface ResourceCollectionProcessable
{
    /**
     * Get resource collection and process it with sorting, filtering, selecting and paginating
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  string  $transformerClass
     * @return  \Illuminate\Pagination\LengthAwarePaginator
     */
    public function processCollection(Builder $builder, string $transformerClass = '') : LengthAwarePaginator;

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $instance
     * @return  \Illuminate\Database\Eloquent\Model
     */
    public function processInstance(Model $instance) : Model;
}
