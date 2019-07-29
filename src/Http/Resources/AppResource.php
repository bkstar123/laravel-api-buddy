<?php

namespace Bkstar123\ApiBuddy\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Bkstar123\ApiBuddy\Traits\ResourceMappingFilter;

abstract class AppResource extends JsonResource
{
    use ResourceMappingFilter;

    /**
     * Specify the resource mapping
     *
     * @return array
     */
    abstract protected function resourceMapping();

    /**
     * Transform the given resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $mapping = $this->resourceMapping();

        $mapping = $this->filterMapping($mapping);

        return $mapping;
    }
}
