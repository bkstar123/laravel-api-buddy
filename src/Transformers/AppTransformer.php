<?php
/**
 * AppTransformer
 *
 * @author: tuanha
 * @last-mod: 29-July-2019
 */
namespace Bkstar123\ApiBuddy\Transformers;

use Bkstar123\ApiBuddy\Contracts\Transformer;

class AppTransformer implements Transformer
{
    /**
     * Transformed keys -> Original keys mapping
     *
     * @var array
     */
    protected static $transformedKeys;

    /**
     * Convert a given transformed attribute to its original
     *
     * @param string $index
     * @return string|null
     */
    public static function originalAttribute($index)
    {
        $attributes = static::$transformedKeys;

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    /**
     * Convert a original attribute to its transformed version
     *
     * @param string $index
     * @return string|null
     */
    public static function transformedAttribute($index)
    {
        $attributes = array_flip(static::$transformedKeys);

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
