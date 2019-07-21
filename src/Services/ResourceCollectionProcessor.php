<?php
/**
 * ApiResponseProcessor.php
 *
 * @author: tuanha
 * @last-mod: 11-July-2019
 */
namespace Bkstar123\ApiBuddy\Services;

use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Bkstar123\ApiBuddy\Contracts\ResourceCollectionProcessable;

class ResourceCollectionProcessor implements ResourceCollectionProcessable
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginateData($builder) : \Illuminate\Pagination\LengthAwarePaginator
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
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function filterData($builder)
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
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function sortData($builder)
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
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function selectFields($builder)
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
