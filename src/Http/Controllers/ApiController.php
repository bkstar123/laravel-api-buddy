<?php
/**
 * ApiController.php
 *
 * @author: tuanha
 * @last-mod: 11-July-2019
 */
namespace Bkstar123\ApiBuddy\Http\Controllers;

use Bkstar123\ApiBuddy\Traits\ApiResponser;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Bkstar123\ApiBuddy\Contracts\ResourceCollectionProcessable;

class ApiController extends BaseController
{
    use ApiResponser, AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var \Bkstar123\ApiBuddy\Contracts\ResourceCollectionProcessable
     */
    protected $processor;

    /**
     * @param  \Bkstar123\ApiBuddy\Contracts\ApiResponseProcessor  $processor
     * @return void
     */
    public function __construct(ResourceCollectionProcessable $processor)
    {
        $this->processor = $processor;
    }
}