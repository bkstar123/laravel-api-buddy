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
     * @param  mixed  $errors
     * @param  int  $status  
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($errors, int $status = 500) : \Illuminate\Http\JsonResponse
    {
        return response()->json(['errors' => $errors, 'code' => $status], $status);
    }

    /**
     * Send success response in JSON format
     *
     * @param  mixed  $data
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data, int $status = 200) : \Illuminate\Http\JsonResponse
    {
        if (is_array($data) && array_key_exists('data', $data)) {
            return response()->json($data, $status);
        }

        return response()->json(['data' => $data], $status);
    }
}
