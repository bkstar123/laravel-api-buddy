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
use Bkstar123\ApiBuddy\Traits\ApiResponseProcessor;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ApiController extends BaseController
{
    use ApiResponser, ApiResponseProcessor, AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}