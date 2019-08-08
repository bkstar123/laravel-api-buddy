<?php
/**
 * Template for transformer class generation
 *
 * @author: tuanha
 * @dlast-mod: 07-Aug-2019
 */

$data = <<<END
<?php
/**
 * $name transformer
 */
namespace App\Transformers;

use Bkstar123\ApiBuddy\Transformers\AppTransformer;

class $name extends AppTransformer
{
    /**
     * Transformed keys -> Original keys mapping
     *
     * @var array
     */
    protected static \$transformedKeys = [
        //'transformed_key' => 'original_key'
    ];

    /**
     * Original keys -> Transformed keys mapping
     *
     * @var array
     */
    protected static \$originalKeys = [
        //'original_key' => 'transformed_key'
    ];
}
END;

return $data;
