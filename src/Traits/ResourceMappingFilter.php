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
     * Add filtering to transform only the keys available in the procied resources
     *
     * @param array  $mapping
     * @return array
     */
    protected function filterMapping(array $mapping)
    {
        foreach ($mapping as $key => $value) {
            if (gettype($value) === 'object' && get_class($value) === 'Illuminate\Support\Carbon') {
                $mapping[$key] = $this->when(isset($value), (string) $value);
            } else {
                $mapping[$key] = $this->when(isset($value), $value);
            }
        }
        return $mapping;
    }
}
