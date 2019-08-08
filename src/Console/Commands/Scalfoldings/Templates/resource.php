<?php
/**
 * Template for resource class generation
 *
 * @author: tuanha
 * @dlast-mod: 08-Aug-2019
 */

$data = <<<END
<?php
/**
 * $name resource
 */
namespace App\Http\Resources;

use Bkstar123\ApiBuddy\Http\Resources\AppResource;

class $name extends AppResource
{
    /**
     * Specify the resource mapping
     *
     * @return array
     */
    protected function resourceMapping()
    {
        return [
            //transformed_key => \$this->{original_key}
            //...
        ];
    }

    protected function afterFilter(\$mapping)
    {
        //Modify the \$mapping

        return \$mapping;
    }
}
END;

return $data;
