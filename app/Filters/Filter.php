<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class Filter
{
    /**
     * @var Request;
     */
    protected $request;
    /**
     * @var Builder
     */
    protected $builder;

    protected $filters = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
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
            $method = camel_case($filter);
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
        return $this->request->only($this->filters);
    }

    /**
     * 编辑模式过滤器，显示软删除和隐藏的
     *
     * @param $editMode
     */
    protected function editMode($editMode)
    {
        $this->builder = $this->builder->withTrashed()->withoutGlobalScope('onlyShown');
    }
}
