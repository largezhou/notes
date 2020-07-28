<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class Filter
{
    /**
     * @var array;
     */
    protected $data;
    /**
     * @var Builder
     */
    protected $builder;

    protected $filters = [];

    public function __construct($data = null)
    {
        if ($data === null) {
            $data = request()->all();
        }

        $this->data = $data;
    }

    /**
     * 应用所有过滤器
     *
     * @param Builder $builder
     *
     * @return mixed
     */
    public function apply(Builder $builder)
    {
        $this->builder = $builder;

        foreach ($this->getFilters() as $filter => $value) {
            $method = Str::camel($filter);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $builder;
    }

    /**
     * 按有的过滤器字段，从请求中获取相应的数据
     *
     * @return array
     */
    protected function getFilters()
    {
        return Arr::only($this->data, $this->filters);
    }

    public function only(array $only)
    {
        $this->filters = array_intersect($this->filters, $only);

        return $this;
    }

    protected function sortField($field)
    {
        if (!($type = Arr::get($this->data, '_sort_type'))) {
            return;
        }

        $this->builder->orderBy($field, $type);
    }
}
