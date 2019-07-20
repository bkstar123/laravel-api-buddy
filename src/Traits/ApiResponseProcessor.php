<?php
/**
 * ApiResponseProcessor.php
 *
 * @author: tuanha
 * @last-mod: 11-July-2019
 */
namespace Bkstar123\ApiBuddy\Traits;

use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait ApiResponseProcessor
{
    /**
     * Paginate data by given limit & page
     * Query example: ?limit=X&page=Y
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function paginateData($builder) : \Illuminate\Pagination\LengthAwarePaginator
    {
        $this->validateInputFor('paginateData', $builder);

        $rules = [
            'limit' => 'integer|min:1|max:' . config('bkstar123_apibuddy.max_per_page'),
        ];

        Validator::validate(request()->all(), $rules);

        if (request()->filled('limit')) {
            $limit = request()->input('limit');  
        } else {
            $limit = config('bkstar123_apibuddy.default_per_page');  
        }
        return $builder->paginate($limit)->appends(request()->query());
    }

    /**
     * Filtering the data by conditions
     * Query example: ?foo=bar&baz=boo
     *
     * @param  $builder  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function filterData($builder)
    {
        $this->validateInputFor('filterData', $builder);

        foreach (request()->query() as $query => $value) {
            if ($query != 'sort_by' && $query != 'limit' && $query != 'fields' && $query != 'page') {
                if (isset($query, $value)) {
                    $builder = $builder->where($query, $value);
                }
            }
        }

        return $builder;
    }

    /**
     * Sorting the data by the given single column
     * Query example: ?sort_by=identifier1,-identifier2 where: "-" indicates descending sorting order
     *
     * @param  $builder  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function sortData($builder)
    {
        $this->validateInputFor('sortData', $builder);

        if (request()->filled('sort_by')) {
            $sortCols = request()->input('sort_by');
            $sortCols = explode(',', $sortCols);
            
            foreach ($sortCols as $sortCol) {
                $order = starts_with($sortCol, '-') ? 'desc' : 'asc';
                $sortCol = ltrim($sortCol, '-');
                $builder = $builder->orderBy($sortCol, $order);
            }
        }

        return $builder;
    }

    /**
     * Specify the fields which are to be included in the data
     * Query example: ?fields=field1,field2
     *
     * @param   $builder  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function selectFields($builder)
    {
        $this->validateInputFor('selectFields', $builder);

        if (request()->filled('fields')) {
            $fields = request()->input('fields');
            $fields = explode(',', $fields);
            
            foreach ($fields as $field) {
                $builder = $builder->addSelect(trim($field));
            }
        }
        
        return $builder;
    }

    /**
     * Get resource data and process it with sorting, filtering, selecting and paginating
     *
     * @param  $builder  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     * @return array
     */
    protected function getData($builder) : array
    {
        $builder =  $this->filterData($builder);
        $builder =  $this->sortData($builder);
        $builder = $this->selectFields($builder);
        
        return $this->paginateData($builder)->toArray();
    }

    /**
     * Validate the input for the given function name
     *
     * @param  string  $function_name
     * @param  mixed  $builder
     * @return true|Exception
     */
    private function validateInputFor($funcion_name, $builder)
    {
        if (!($builder instanceof QueryBuilder) && !($builder instanceof EloquentBuilder)) {
            throw new Exception('Invalid parameter given to '.$funcion_name.'() in the '.
                      debug_backtrace()[1]['function'].
                      '() method of the '.
                      debug_backtrace()[1]['class'].
                      'class, it must be an instance of either '.
                      EloquentBuilder::class.
                      ' or '.
                      QueryBuilder::class.
                      ' classes');
        }

        return true;
    }
}
