<?php
/**
 * Template for controller class generation
 *
 * @author: tuanha
 * @dlast-mod: 10-Aug-2019
 */

$data = <<<END
<?php
/**
 * $name API controller
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class $name extends Controller
{
    //
}
END;

return $data;
