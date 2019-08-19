<?php
/**
 * CollectionBasicHandling Trait
 *
 * @author: tuanha
 * @last-mod: 29-July-2019
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
        if (request()->filled('limit')) {
            $rules = [
                'limit' => 'integer|min:1|max:' . config('bkstar123_apibuddy.max_per_page'),
            ];
            Validator::validate(request()->all(), $rules);
            $limit = request()->input('limit');
        } else {
            $limit = config('bkstar123_apibuddy.default_per_page');
        }
        return $builder->paginate($limit)->appends(request()->query());
    }

    /**
     * @param  \EloquentBuilder|\QueryBuilder  $builder
     * @param  string $transformerClass
     * @return \EloquentBuilder|\QueryBuilder
     */
    public function filterData($builder, $transformerClass = '')
    {
        $validOpKeys = ['gt', 'gte', 'lt', 'lte', 'neq', 'eq'];
        $opMapping = [
            'gt' => '>',
            'gte' => '>=',
            'lt' => '<',
            'lte' => '<=',
            'neq' => '<>',
            'eq' => '='
        ];
        $reservedQueries = ['sort_by', 'limit', 'fields', 'page'];
        $tableName = $this->getTableName($builder);
        foreach (request()->query() as $query => $value) {
            if (!in_array($query, $reservedQueries)) {
                if (isset($query, $value)) {
                    $opKey = 'eq';
                    if (preg_match('/(.+)\{(.+)\}$/', $query, $matches) === 1) {
                        if (in_array($matches[2], $validOpKeys)) {
                            $opKey = $matches[2];
                        }
                        $query = $matches[1];
                    }
                    if (config('bkstar123_apibuddy.useTransform') && !empty($transformerClass)) {
                        $query = $transformerClass::originalAttribute($query);
                    }
                    is_null($query) ?: $builder = $builder->where($tableName.'.'.$query, $opMapping[$opKey], $value);
                }
            }
        }
        return $builder;
    }

    /**
     * @param  \EloquentBuilder|\QueryBuilder  $builder
     * @param  string $transformerClass
     * @return \EloquentBuilder|\QueryBuilder
     */
    public function sortData($builder, $transformerClass = '')
    {
        if (request()->filled('sort_by')) {
            $sortCols = request()->input('sort_by');
            $sortCols = explode(',', $sortCols);
            $tableName = $this->getTableName($builder);
            foreach ($sortCols as $sortCol) {
                $order = starts_with($sortCol, '-') ? 'desc' : 'asc';
                $sortCol = ltrim($sortCol, '-');
                if (config('bkstar123_apibuddy.useTransform') && !empty($transformerClass)) {
                    $sortCol = $transformerClass::originalAttribute($sortCol);
                }
                is_null($sortCol) ?: $builder = $builder->orderBy($tableName.'.'.$sortCol, $order);
            }
        }
        return $builder;
    }

    /**
     * @param  \EloquentBuilder|\QueryBuilder  $builder
     * @return \EloquentBuilder|\QueryBuilder
     */
    public function selectFields($builder)
    {
        if (!config('bkstar123_apibuddy.useTransform')) {
            if (request()->filled('fields')) {
                $fields = request()->input('fields');
                $fields = explode(',', $fields);
                $tableName = $this->getTableName($builder);
                foreach ($fields as $field) {
                    $builder = $builder->addSelect($tableName.'.'.trim($field));
                }
            }
        }
        // In case of using transformation, field selection is done via the transformation
        return $builder;
    }
    
    /**
     * @param  \EloquentBuilder|\QueryBuilder  $builder
     * @return  mixed string|null
     */
    final private function getTableName($builder)
    {
        switch (get_class($builder)) {
            case EloquentBuilder::class:
                return $builder->getQuery()->from;
                break;
            case QueryBuilder::class:
                return $builder->from;
                break;
            default:
                return null;
                break;
        }
    }
}
