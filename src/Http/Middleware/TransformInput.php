<?php
/**
 * TransformInput middleware
 * Convert the input attributes to their original versions before proceeding a request modifying resources (like POST, PUT, PATCH, DELETE)
 * and convert the original attributes to their transformed versions before sending validation error response
 *
 * @author: tuanha
 * last-mod: 28-July-2019
 */
namespace Bkstar123\ApiBuddy\Http\Middleware;

use Closure;
use Illuminate\Validation\ValidationException;

class TransformInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param   FQCN-string  $transformerClass
     * @return  mixed
     */
    public function handle($request, Closure $next, $transformerClass)
    {
        $transformedInput = [];
        foreach ($request->request->all() as $input => $value) {
            $transformedInput[$transformerClass::originalAttribute($input)] = $value;
        }
        $request->replace($transformedInput);
        $response = $next($request);
        if ($response->exception && $response->exception instanceof ValidationException) {
            $transformedErrors = [];
            $data = $response->getData();
            foreach ($data->errors as $field => $error) {
                $transformedField = $transformerClass::transformedAttribute($field);
                $transformedErrors[$transformedField] = str_replace($field, $transformedField, $error);
            }
            $data->errors = $transformedErrors;
            $response->setData($data);
        }
        return $response;
    }
}
