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
     * Paginate data by the given limit & page
     * Query example: ?limit=X&page=Y
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
     public function paginateData($builder) : \Illuminate\Pagination\LengthAwarePaginator;

    /**
     * Filtering the data by the given conditions
     * Query example: ?foo=bar&baz=boo
     *
     * @param  $builder  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
     public function filterData($builder);

    /**
     * Sorting the data by the given columns
     * Query example: ?sort_by=identifier1,-identifier2 where: "-" indicates descending sorting order
     *
     * @param  $builder  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
     public function sortData($builder);

    /**
     * Specify the fields which are to be included in the returned data
     * Query example: ?fields=field1,field2
     *
     * @param   $builder  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
     public function selectFields($builder);

    /**
     * Get resource collection and process it with sorting, filtering, selecting and paginating
     *
     * @param  $builder  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     * @return array
     */
    public function getCollection($builder) : array;
}