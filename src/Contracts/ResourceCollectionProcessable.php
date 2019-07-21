<?php
/**
 * ResourceCollectionProcessable.php
 *
 * @author: tuanha
 * @last-mod: 21-July-2019
 */
namespace Bkstar123\ApiBuddy\Contracts;

interface ResourceCollectionProcessable
{
    /**
     * Get resource collection and process it with sorting, filtering, selecting and paginating
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @return array
     */
    public function processCollection($builder) : array;

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $instance
     * @return array
     */
    public function processInstance($instance) : array;
}