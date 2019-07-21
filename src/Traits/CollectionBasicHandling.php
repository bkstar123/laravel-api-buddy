<?php
/**
 * CollectionBasicHandling Trait
 *
 * @author: tuanha
 * @last-mod: 21-July-2019
 */
namespace Bkstar123\ApiBuddy\Traits;

use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait CollectionBasicHandling
{
	/**
     * @param  \EloquentBuilder|\QueryBuilder  $builder
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
     * @param  \EloquentBuilder|\QueryBuilder  $builder
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function filterData($builder)
    {
        $this->validateInputFor('filterData', $builder);
        $validOpKeys = ['gt', 'gte', 'lt', 'lte', 'neq'];
        $opMapping = [
            'gt' => '>',
            'gte' => '>=',
            'lt' => '<',
            'lte' => '<=',
            'neq' => '<>',
            'eq' => '='
        ];
        foreach (request()->query() as $query => $value) {
            if ($query != 'sort_by' && $query != 'limit' && $query != 'fields' && $query != 'page') {
                if (isset($query, $value)) {
                    $opKey = 'eq';
                    if (preg_match('/(.+)\{(.+)\}$/', $query, $matches) === 1) {
                        if (in_array($matches[2], $validOpKeys)) {
                            $opKey = $matches[2];
                        } 
                        $query = $matches[1];
                    }
                    $builder = $builder->where($query, $opMapping[$opKey], $value);
                }
            }
        }
        return $builder;
    }

    /**
     * @param  \EloquentBuilder|\QueryBuilder  $builder
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
     * @param  \EloquentBuilder|\QueryBuilder  $builder
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