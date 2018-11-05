<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use phpDocumentor\Reflection\Types\Parent_;

class ResourceCollection extends AnonymousResourceCollection
{
    /**
     * @param $keys
     *
     * @see \App\Http\Resources\JsonResource::only()
     * @return \App\Http\Resources\ResourceCollection
     */
    public function only($keys)
    {
        return $this->applyFilter('only', $keys);
    }

    /**
     * @param $keys
     *
     * @see \App\Http\Resources\JsonResource::except()
     * @return \App\Http\Resources\ResourceCollection
     */
    public function except($keys)
    {
        return $this->applyFilter('except', $keys);
    }

    /**
     * 应用过滤方法
     *
     * @param string       $type except or only
     * @param string|array $keys
     *
     * @return $this
     */
    protected function applyFilter($type, $keys)
    {
        $this->collection->map(function (JsonResource $resource) use ($type, $keys) {
            $resource->$type($keys);
        });

        return $this;
    }

    public function toArray($request)
    {
        $data = $this->collection->map->toArray($request)->all();

        return $data;
    }
}
