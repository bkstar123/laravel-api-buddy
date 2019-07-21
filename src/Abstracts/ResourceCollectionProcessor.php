<?php
/**
 * ResourceCollectionProcessor.php
 *
 * @author: tuanha
 * @last-mod: 21-July-2019
 */
namespace Bkstar123\ApiBuddy\Abstracts;

use Bkstar123\ApiBuddy\Contracts\ResourceCollectionProcessable;

abstract class ResourceCollectionProcessor implements ResourceCollectionProcessable
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @return array
     */
    public function processCollection($builder) : array
    {
        $builder =  $this->filterData($builder);
        $builder =  $this->sortData($builder);
        $builder = $this->selectFields($builder);
        
        return $this->paginateData($builder)->toArray();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $instance
     * @return array
     */
    public function processInstance($instance) : array
    {
        $builder = $instance->getQuery();
        return $this->selectFields($builder)->where('id', $instance->id)->get()->toArray();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    abstract public function filterData($builder);

    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    abstract public function sortData($builder);

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
