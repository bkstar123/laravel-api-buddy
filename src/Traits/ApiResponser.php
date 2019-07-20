<?php
/**
 * ApiResponser.php
 *
 * @author: tuanha
 * @last-mod: 11-July-2019
 */
namespace Bkstar123\ApiBuddy\Traits;

trait ApiResponser
{
    /**
     * Send error response in JSON format
     *
     * @param mixed $message  array|string
     * @param integer $code  HTTP Status Code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($message, $status = 500)
    {
        return response()->json(['error' => $message, 'code' => $status], $status);
    }

    /**
     * Send success response in JSON format
     *
     * @param mixed $data
     * @param integer $code  HTTP Status Code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data, $status = 200)
    {
        if (is_array($data) && array_key_exists('data', $data)) {
            return response()->json($data, $status);
        }

        return response()->json(['data' => $data], $status);
    }
}
