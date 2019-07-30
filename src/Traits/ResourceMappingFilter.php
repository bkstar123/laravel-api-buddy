<?php
/**
 * ResourceMappingFilter trait
 * Filter the API Resource mapping to keep only the fields given in the request
 *
 * @author: tuanha
 * @last-mod: 29-July-2019
 */
namespace Bkstar123\ApiBuddy\Traits;

trait ResourceMappingFilter
{
    /**
     * Filter the mapping according to the fields given in the request
     *
     * @param array  $mapping
     * @return array
     */
    protected function filterMapping($mapping)
    {
        if (request()->filled('fields')) {
            $fields = request()->input('fields');
            $fields = explode(',', $fields);
            $fields = collect($fields)->filter(function ($field, $key) use ($mapping) {
                return in_array($field, array_keys($mapping));
            })->toArray();
            if (!empty($fields)) {
                foreach ($mapping as $key => $value) {
                    if (!in_array($key, $fields)) {
                        unset($mapping[$key]);
                    }
                }
            }
        }
        return $mapping;
    }
}
