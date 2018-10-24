<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CanHide
{
    public static function bootCanHide()
    {
        static::addGlobalScope('onlyShown', function (Builder $builder) {
            $builder->where('hidden', false);
        });
    }

    public function scopeWithHidden(Builder $query)
    {
        $query->withoutGlobalScope('onlyShown');
    }

    /**
     * CanHide 模型启用编辑模式，需要显示隐藏的记录
     *
     * @param Builder $query
     */
    public function scopeEditMode(Builder $query)
    {
        $query->withTrashed()->withHidden();
    }
}
