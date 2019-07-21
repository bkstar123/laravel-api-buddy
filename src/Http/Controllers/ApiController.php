<?php
/**
 * ApiController.php
 *
 * @author: tuanha
 * @last-mod: 11-July-2019
 */
namespace Bkstar123\ApiBuddy\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Bkstar123\ApiBuddy\Contracts\ApiResponsible;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var \Bkstar123\ApiBuddy\Contracts\ApiResponsible
     */
    protected $apiResponser;

    /**
     * @param  \Bkstar123\ApiBuddy\Contracts\ApiResponsible  $apiResponser
     * @return void
     */
    public function __construct(ApiResponsible $apiResponser)
    {
        $this->apiResponser = $apiResponser;
    }
}
