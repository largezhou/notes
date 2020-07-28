<?php

namespace App\Models;

use App\Filters\Filter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class Model extends \Illuminate\Database\Eloquent\Model
{
    protected $casts = [
        'created_at' => 'string',
        'updated_at' => 'string',
        'deleted_at' => 'string',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected static function boot()
    {
        // 如果已登录，且请求中有 edit_mode 字段，则去掉软删除的全局作用域
        // 这里一定要放在 parent::boot 之前，因为应用 全局作用域 是有顺序的
        static::addGlobalScope('withTrashed', function (Builder $builder) {
            if (auth()->check() && request()->header('Edit-Mode')) {
                $builder->withoutGlobalScope(SoftDeletingScope::class);
            }
        });

        parent::boot();
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
        return parent::update(...func_get_args());
    }

    public function scopeShowAll(Builder $query)
    {
        $query->withoutGlobalScope(SoftDeletingScope::class);
    }
}
