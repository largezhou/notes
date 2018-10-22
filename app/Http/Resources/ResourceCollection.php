<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

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

        if ($this->resource instanceof AbstractPaginator) {
            return [
                'data' => $data,
                'page' => [
                    'from'         => $this->resource->firstItem(),
                    'last_page'    => $this->resource->lastPage(),
                    'per_page'     => $this->resource->perPage(),
                    'to'           => $this->resource->lastItem(),
                    'total'        => $this->resource->total(),
                    'current_page' => $this->resource->currentPage(),
                ],
            ];
        } else {
            return $data;
        }
    }

    public function toResponse($request)
    {
        return $this->toArray($request);
    }
}
