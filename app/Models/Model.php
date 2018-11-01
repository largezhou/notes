<?php

namespace App\Models;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Model extends \Illuminate\Database\Eloquent\Model
{
    protected $casts = [
        'created_at' => 'string',
        'updated_at' => 'string',
        'deleted_at' => 'string',
    ];

    /**
     * 编辑模式
     *
     * @param Builder $query
     */
    public function scopeEditMode(Builder $query)
    {
        $query->withTrashed();
    }

    /**
     * 应用过滤器
     *
     * @param Builder $query
     * @param Filter  $filter
     *
     * @return mixed
     */
    public function scopeFilter(Builder $query, Filter $filter)
    {
        return $filter->apply($query);
    }

    public function update(array $attributes = [], array $options = [])
    {
        if (
            array_has($attributes, 'deleted_at') &&
            $attributes['deleted_at'] === null &&
            trait_exists(SoftDeletes::class)
        ) {
            return $this->restore();
        }

        return parent::update(...func_get_args());
    }
}
