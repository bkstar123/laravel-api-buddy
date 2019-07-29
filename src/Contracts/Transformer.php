<?php
/**
 * Transformer Contract
 *
 * @author: tuanha
 * @last-mod:21-July-2019
 */
namespace Bkstar123\ApiBuddy\Contracts;

interface Transformer
{
    /**
     * Convert a given transformed attribute to its original
     *
     * @param string $index
     * @return string|null
     */
    public static function originalAttribute($index);

    /**
     * Convert a original attribute to its transformed version
     *
     * @param string $index
     * @return string|null
     */
    public static function transformedAttribute($index);
}
